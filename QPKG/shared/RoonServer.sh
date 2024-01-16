#!/bin/sh
CONF=/etc/config/qpkg.conf
QPKG_NAME="RoonServer"
QPKG_ROOT=`/sbin/getcfg $QPKG_NAME Install_Path -f ${CONF}`
WEB_PATH="/home/httpd"
WEBUI=$(/sbin/getcfg $QPKG_NAME webUI -f ${CONF});
QTS_VER=$(/sbin/getcfg system version)
QTS_BUILD=$(getcfg system 'Build Number')
QNAP_MEMTOTAL=`awk '/MemTotal/ {print $2}' /proc/meminfo`
QNAP_MEMFREE=`awk '/MemFree/ {print $2}' /proc/meminfo`
QPKG_VERSION=`/sbin/getcfg $QPKG_NAME Version -f ${CONF}`
MULTIMEDIA_DISABLE=`/sbin/getcfg DISABLE HomeFeature -f /var/.application.conf`
MAJOR_QTS_VER=`echo "$QTS_VER" | tr -d '.' | cut -c1-2`
ARCH=$(uname -m)
MODEL=`getsysinfo model`
QNAP_SERIAL=`get_hwsn`
MTU=`ifconfig | grep eth[0-9] -A1 | grep MTU | grep MTU | cut -d ":" -f 2 | awk '{print $1}' | xargs | sed "s/ ---- /\n---- /g"`
ROON_VERSION=`cat "${QPKG_ROOT}/RoonServer/VERSION"`
ROON_LIB_DIR="${QPKG_ROOT}/lib64"
ROON_QTS42_LIB_DIR="${QPKG_ROOT}/lib64_ForQTS4.2"
ROON_TMP_DIR="${QPKG_ROOT}/tmp"
ROON_ID_DIR="${QPKG_ROOT}/id"
ROON_DATABASE_DIR=`/sbin/getcfg $QPKG_NAME DB_Path -f /etc/config/qpkg.conf`
ROON_DATAROOT="${ROON_DATABASE_DIR}/RoonOnNAS"
ROON_DATABASE_DIR_FS=`df -PThi "${ROON_DATAROOT}" | awk '{print $2}' | tail -1`
ROON_DATABASE_DIR_FREE_INODES=`df -PThi "${ROON_DATAROOT}" | awk '{print $5}' | tail -1`
ROON_FFMPEG_DIR="${ROON_DATAROOT}/bin"
ALSA_CONFIG_PATH="${QPKG_ROOT}/etc/alsa/alsa.conf"
ROON_LOG_FILE="${ROON_DATAROOT}/RoonOnNAS.log.txt"
QTS_INSTALLED_APPS=`cat /etc/config/qpkg.conf | grep "\[" | sed 's/[][]//g' | tr '\n' ', '`
PID=$(/bin/ps aux | grep "${QPKG_ROOT}/RoonServer/start.sh" | grep -v grep | awk '{print $1}')

ST_COLOR="\033[38;5;34m"
HL_COLOR="\033[38;5;197m"
REG_COLOR="\033[0m"

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

info ()
{
   ## Echoing System Info
   echolog "ROON_DATABASE_DIR" "${ROON_DATAROOT} - [`[ -d \"${ROON_DATAROOT}\" ] && echo \"available\" || echo \"not available\"`]"
   echolog "ROON_DATABASE_DIR_FS" "${ROON_DATABASE_DIR_FS}"
   echolog "ROON_ID_DIR" "$ROON_ID_DIR - [`[ -d \"$ROON_ID_DIR\" ] && echo \"available\" || echo \"not available\"`]"
   echolog "Free Inodes" "${ROON_DATABASE_DIR_FREE_INODES}"
   echolog "ROON_DIR" "${QPKG_ROOT}"
   echolog "Model" "${MODEL}"
   echolog "QNAP Serial" "${QNAP_SERIAL}"
   echolog "Architecture" "${ARCH}"
   echolog "Total Memory" "${QNAP_MEMTOTAL}"
   echolog "Available Memory" "${QNAP_MEMFREE}"
   echolog "QTS Version" "${QTS_VER} - Build: ${QTS_BUILD}"
   echolog "PKG Version" "${QPKG_VERSION}"
   echolog "Installed QTS Apps" "${QTS_INSTALLED_APPS}"
   echolog "Hostname" "${HOSTNAME}"
   echolog "MTU" "${MTU}"
}

RoonOnNAS_folderCheck ()
{
  if [ -d "${ROON_DATABASE_DIR}" ]; then
    [ -d "${ROON_DATABASE_DIR}/RoonOnNAS" ] || mkdir "${ROON_DATABASE_DIR}/RoonOnNAS"
    [ -d "${ROON_DATABASE_DIR}/RoonOnNAS/bin" ] || mkdir "${ROON_DATABASE_DIR}/RoonOnNAS/bin"
  fi
}

start_RoonServer () {
  if [ "${ROON_DATAROOT}" != "/RoonOnNAS" ] && [ -d "${ROON_DATAROOT}" ]; then

      ## Fix missing executable permission for ffmpeg
      [ -f "${ROON_FFMPEG_DIR}/ffmpeg" ] && [ ! -x "${ROON_FFMPEG_DIR}/ffmpeg" ] && chmod 755 "${ROON_FFMPEG_DIR}/ffmpeg"

      export PATH="${ROON_FFMPEG_DIR}:$PATH"

      echo "" | tee -a "$ROON_LOG_FILE"
      echo "############### Used FFMPEG Version ##############" | tee -a "$ROON_LOG_FILE"
      echo -e $(ffmpeg -version) | tee -a "$ROON_LOG_FILE"
      echo "##################################################" | tee -a "$ROON_LOG_FILE"
      echo "" | tee -a "$ROON_LOG_FILE"


      export ROON_DATAROOT


      LD_LIBRARY_PATH=/lib64:/lib:${ROON_LIB_DIR}:${LD_LIBRARY_PATH}
      if [ $MAJOR_QTS_VER -lt 43 ]; then
        LD_LIBRARY_PATH=${ROON_QTS42_LIB_DIR}:${LD_LIBRARY_PATH}
      fi

      export LD_LIBRARY_PATH
      export ALSA_CONFIG_PATH
      export ROON_INSTALL_TMPDIR="${ROON_TMP_DIR}"
      export TMP="${ROON_TMP_DIR}"
      export TMPDIR="${ROON_TMP_DIR}"
      export ROON_ID_DIR

      ## Creating required directories, if they do not exist
      [ -d "$ROON_ID_DIR" ] || mkdir "$ROON_ID_DIR"
      [ -d "$ROON_TMP_DIR" ] || mkdir "$ROON_TMP_DIR"


      # Checking for additional start arguments.
      if [[ -f "${ROON_DATAROOT}/ROON_DEBUG_LAUNCH_PARAMETERS.txt" ]]; then
          ROON_ARGS=`cat "${ROON_DATAROOT}/ROON_DEBUG_LAUNCH_PARAMETERS.txt" | xargs | sed "s/ ---- /\n---- /g"`
          echolog "ROON_DEBUG_ARGS" "${ROON_ARGS}"
      else
          ROON_ARGS=""
      fi

      ## Start RoonServer
      setcfg ${QPKG_NAME} MULTIMEDIA_DISABLE_ON_START ${MULTIMEDIA_DISABLE} -f "${CONF}"
      ${QPKG_ROOT}/RoonServer/start.sh "${ROON_ARGS}" | while read line; do echo `date +%d.%m.%y-%H:%M:%S` " --- $line"; done >> "$ROON_LOG_FILE"  2>&1 &
      PID=$(ps aux | grep "${QPKG_ROOT}/RoonServer/start.sh" | grep -v grep | awk '{print $1}')
      echolog "RoonServer PID" "${PID}"

      echo "" | tee -a "$ROON_LOG_FILE"
      echo "" | tee -a "$ROON_LOG_FILE"
      echo "########## Installed RoonServer Version ##########" | tee -a "$ROON_LOG_FILE"
      echo "${ROON_VERSION}" | tee -a "$ROON_LOG_FILE"
      echo "##################################################" | tee -a "$ROON_LOG_FILE"
      echo "" | tee -a "$ROON_LOG_FILE"
      echo "" | tee -a "$ROON_LOG_FILE"
  fi

}

start_daemon ()
{
        info
        #Launch the service in the background if RoonServer share exists.
        ln -sfn "${QPKG_ROOT}/web" "${WEB_PATH}${WEBUI}"
        start_RoonServer
        }

case "$1" in
  start)
    ENABLED=$(/sbin/getcfg $QPKG_NAME Enable -u -d FALSE -f $CONF)
    RoonOnNAS_folderCheck
    if [ "$ENABLED" != "TRUE" ]; then
        echolog "$QPKG_NAME is disabled."
        exit 1
    fi
    if kill -s 0 $PID; then
        echolog "${QPKG_NAME} is already running with PID: $PID"
    else
        echo "" > "$ROON_LOG_FILE"
        echolog "Starting ${QPKG_NAME} ..."
        start_daemon
    fi
    ;;

  stop)
    if kill -s 0 $PID; then
        echolog "Stopping RoonServer..."
        echolog "Roon PID to be killed" "$PID"
        kill ${PID} >> "$ROON_LOG_FILE"
        rm -rf "${ROON_TMP_DIR}"/*
        if [[ $2 != "keepwebalive" ]]; then
           rm -rf "${QPKG_ROOT}/web/tmp"/*
           rm  "${WEB_PATH}${WEBUI}"
        fi
        echolog "RoonServer has been stopped."
    else
        echolog "${QPKG_NAME} is not running."
    fi
    ;;

  restart)
    isRestart=true
    $0 stop
    $0 start
    ;;

  *)
    echo "Usage: $0 {start|stop|restart}"
    exit 1
esac

exit 0
