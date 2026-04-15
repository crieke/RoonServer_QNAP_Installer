#!/bin/sh
CONF=/etc/config/qpkg.conf
QPKG_NAME="RoonServer"
QPKG_ROOT=`/sbin/getcfg $QPKG_NAME Install_Path -f ${CONF}`

QCS_NAME="container-station"
QCS_QPKG_DIR=$(/sbin/getcfg $QCS_NAME Install_Path -f ${CONF})
DOCKER_CMD="${QCS_QPKG_DIR}/bin/system-docker"
CONTAINER_NAME=roonserver
COMPOSE_YML_DIR="${QPKG_ROOT}/docker/compose"
ROONSERVER_OPTIONS=(`/sbin/getcfg $QPKG_NAME options -f ${CONF}`)
ROON_CHANNEL="production"

WEB_PATH="/home/httpd"
WEBUI=$(/sbin/getcfg $QPKG_NAME webUI -f ${CONF});

ROONONNAS_DIR=`/sbin/getcfg $QPKG_NAME DB_Path -f /etc/config/qpkg.conf`
ROON_DATAROOT="${ROONONNAS_DIR}/RoonOnNAS"
ROON_TMP_DIR="${QPKG_ROOT}/tmp"
ROON_ID_HOST_DIR="${QPKG_ROOT}/id"

QNAP_QTS_VER=$(/sbin/getcfg system version)
QNAP_QTS_BUILD=$(/sbin/getcfg system 'Build Number')
QNAP_MEMTOTAL=`awk '/MemTotal/ {print $2}' /proc/meminfo`
QNAP_MEMFREE=`awk '/MemFree/ {print $2}' /proc/meminfo`
QPKG_VERSION=`/sbin/getcfg $QPKG_NAME Version -f ${CONF}`
MAJOR_QTS_VER=`echo "$QNAP_QTS_VER" | tr -d '.' | cut -c1-2`
ARCH=$(uname -m)

QNAP_MODEL=`/sbin/getsysinfo model`
QNAP_SERIAL=`/sbin/get_hwsn`
ROON_DATABASE_DIR_FS=`df -PThi "${ROON_DATAROOT}" | awk '{print $2}' | tail -1`
ROON_DATABASE_DIR_FREE_INODES=`df -PThi "${ROON_DATAROOT}" | awk '{print $5}' | tail -1`
ROON_FFMPEG_DIR="${ROON_DATAROOT}/bin"
ROON_LOG_FILE="${ROON_DATAROOT}/RoonOnNAS.log.txt"

echo $(basename "$0") >> ${ROON_LOG_FILE}
echo $@ >> ${ROON_LOG_FILE}

ST_COLOR="\033[38;5;34m"
HL_COLOR="\033[38;5;197m"
REG_COLOR="\033[0m"

#specify options
for i in "${ROONSERVER_OPTIONS[@]}"
do
   declare $i=true
done

## Log Function
echolog () {
    TIMESTAMP=$(date +%d.%m.%y-%H:%M:%S)
    if [[ $# == 2 ]]; then
        PARAMETER1=$1
        PARAMETER2=$2
        echo -e "${ST_COLOR}${TIMESTAMP}${REG_COLOR} --- ${HL_COLOR}${PARAMETER1}:${REG_COLOR} ${PARAMETER2}"
        echo "${TIMESTAMP} --- ${PARAMETER1}: ${PARAMETER2}" >> "$ROON_LOG_FILE"
    elif [[ $# == 1 ]]; then
        PARAMETER1=$1
        echo -e "${ST_COLOR}${TIMESTAMP}${REG_COLOR} --- ${PARAMETER1}"
        echo "${TIMESTAMP} --- ${PARAMETER1}" >> "$ROON_LOG_FILE"
    else
        echo -e "The echolog function requires 1 or 2 parameters."
    fi
}

compose_docker_yml_files () {
  COMPOSE_FILES="\
  -f ${COMPOSE_YML_DIR}/roonserver.yml \
  -f ${COMPOSE_YML_DIR}/platform_specific.yml "
  
  [ -z ${smb_cifs+x} ] || COMPOSE_FILES="${COMPOSE_FILES} -f $COMPOSE_YML_DIR/smb_cifs_support.yml"
  ( [ -z ${usb_audio+x} ] && [ -z ${hdmi_audio+x} ] ) || COMPOSE_FILES="${COMPOSE_FILES} -f $COMPOSE_YML_DIR/audio.yml"
  [ -z ${usb_audio+x} ] || COMPOSE_FILES="${COMPOSE_FILES} -f $COMPOSE_YML_DIR/audio_usb.yml"
  [ -z ${hdmi_audio+x} ] || COMPOSE_FILES="${COMPOSE_FILES} -f $COMPOSE_YML_DIR/audio_hdmi.yml"
}

info () {
   ## Echoing System Info
   echolog "ROON_DATABASE_DIR" "${ROON_DATAROOT} - [`[ -d \"${ROON_DATAROOT}\" ] && echo \"available\" || echo \"not available\"`]"
   echolog "ROON_DATABASE_DIR_FS" "${ROON_DATABASE_DIR_FS}"
   echolog "ROON_ID_HOST_DIR" "$ROON_ID_HOST_DIR - [`[ -d \"$ROON_ID_HOST_DIR\" ] && echo \"available\" || echo \"not available\"`]"
   echolog "Free Inodes" "${ROON_DATABASE_DIR_FREE_INODES}"
   echolog "ROON_DIR" "${QPKG_ROOT}"
   echolog "Model" "${QNAP_MODEL}"
   echolog "Architecture" "${ARCH}"
   echolog "Total Memory" "${QNAP_MEMTOTAL}"
   echolog "Available Memory" "${QNAP_MEMFREE}"
   echolog "QTS Version" "${QNAP_QTS_VER} - Build: ${QNAP_QTS_BUILD}"
   echolog "PKG Version" "${QPKG_VERSION}"
   echolog "Hostname" "${HOSTNAME}"
   echolog "Roon-Channel" "${ROON_CHANNEL}"
}

RoonOnNAS_folderCheck () {
  if [ -d "${ROONONNAS_DIR}" ]; then
    [ -d "${ROONONNAS_DIR}/RoonOnNAS" ] || mkdir "${ROONONNAS_DIR}/RoonOnNAS"
    [ -d "${ROONONNAS_DIR}/RoonOnNAS/bin" ] || mkdir "${ROONONNAS_DIR}/RoonOnNAS/bin"
  fi
}

getCSStatus () {
   echo "$(/sbin/getcfg container-station status -f /etc/qpkg_run_status)"
}  

checkCS () {
  case $(getCSStatus) in
    0)
      echolog "Container Station down."
      exit 1;
      ;;
    1)
      echolog "Container Station is starting..."
      SECONDS=0
      until [[  $(getCSStatus) == "2" ]]; do
        if (( SECONDS > 120 )); then
          echolog "Giving up..."
          exit 1
        fi
        echolog "($SECONDS) Container Station is not up yet. Waiting..."
        sleep 5
      done 
      ;;
    2)
      echolog "Container Station is up."
      ;;
  esac
}

export_vars () {
  export ROON_DATAROOT
  export QPKG_ROOT
  export QNAP_MODEL
  export QNAP_SERIAL
  export QNAP_QTS_VER
  export CONTAINER_NAME
  export ROON_CHANNEL
}

start_webpanel () {
        [ -f ${ROON_DATAROOT}/earlyaccess.txt ] && ROON_CHANNEL="earlyaccess"
        #Launch the service in the background if RoonServer share exists.
        ln -sfn "${QPKG_ROOT}/web" "${WEB_PATH}${WEBUI}"
}

start_roonserver () {
  if [ "${ROON_DATAROOT}" != "/RoonOnNAS" ] && [ -d "${ROON_DATAROOT}" ]; then
      compose_docker_yml_files
      export_vars

      ## Creating required directories, if they do not exist
      [ -d "$ROON_ID_HOST_DIR" ] || mkdir "$ROON_ID_HOST_DIR"
      [ -d "$ROON_TMP_DIR" ] || mkdir "$ROON_TMP_DIR"

      ${DOCKER_CMD} compose ${COMPOSE_FILES} up -d  
  fi
}

case "$1" in
  start)
    RS_ENABLED=$(/sbin/getcfg $QPKG_NAME Enable -u -d FALSE -f $CONF)
    CS_ENABLED=$(/sbin/getcfg "container-station" Enable -u -d FALSE -f $CONF)

      if [ "$RS_ENABLED" != "TRUE" ]; then
          echolog "$QPKG_NAME is disabled."
          exit 1
      fi
      if [ "$CS_ENABLED" != "TRUE" ]; then
          echolog "Container Station is disabled."
          exit 1
      fi
      
      CONTAINER_ID=$(${DOCKER_CMD} ps -a -q -f name=$CONTAINER_NAME)
      if [ ! "$CONTAINER_ID" ]; then     
          echolog "Starting Roon Server..."
          info
          checkCS
          RoonOnNAS_folderCheck
          start_webpanel
          start_roonserver
      else
          echolog "${QPKG_NAME} is already running (ID: $CONTAINER_ID)"
      fi
    ;;

  stop)
    CS_ENABLED=$(/sbin/getcfg "container-station" Enable -u -d FALSE -f $CONF)

    # Check if CS has not been stopped before Roon Server
    if [ "$CS_ENABLED" == "TRUE" ]; then
      # -->  CS is still up.
      CONTAINER_ID=$(${DOCKER_CMD} ps -a -q -f name=$CONTAINER_NAME)
      if [ ! "$CONTAINER_ID" ]; then
        # --> No roonserver conatiner running
        echolog "${QPKG_NAME} is not running."
      else
        # --> Stopping roonserver conatiner
        echolog "Stopping RoonServer..."
        compose_docker_yml_files
        export_vars
        ${DOCKER_CMD} compose ${COMPOSE_FILES} down --remove-orphans
        
        if [[ $2 != "keepwebalive" ]]; then
          [ -d "${WEB_PATH}${WEBUI}" ] && rm  "${WEB_PATH}${WEBUI}"
        fi
        
        echolog "RoonServer has been stopped."
      fi
    else
      # -> CS is disabled:
      # Edge case: CS has been stopped before RoonServer. We can assume RoonServer docker is down. Only the web-panel needs to be removed
      [ -d "${WEB_PATH}${WEBUI}" ] && rm  "${WEB_PATH}${WEBUI}"
      echolog "RoonServer is not running."
    fi
    ;;

  restart)
    $0 stop
    $0 start
    ;;
  *)
    echo "Usage: $0 {start|stop|restart}"
    exit 1
esac

exit 0
