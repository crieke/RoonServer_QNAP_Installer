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
ROON_TMP_DIR="${QPKG_ROOT}/tmp"
ROON_PIDFILE="${QPKG_ROOT}/RoonServer.pid"
ROON_DATABASE_DIR=`/sbin/getcfg $QPKG_NAME DB_Path -f /etc/config/qpkg.conf`
ROON_DATABASE_DIR_FS=`df -T "${ROON_DATABASE_DIR}" | grep "^/dev" | awk '{print $2}'`
ALSA_CONFIG_PATH="${QPKG_ROOT}/etc/alsa/alsa.conf"
ROON_LOG_FILE="${QPKG_ROOT}/RoonServer.log"
ROON_DEBUG_EXTERNAL_LOG="${ROON_DATABASE_DIR}/ROONSERVER_QNAP_LOG.txt"
QTS_INSTALLED_APPS=`cat /etc/config/qpkg.conf | grep "\[" | sed 's/[][]//g' | tr '\n' ', '`

ST_COLOR="\033[38;5;34m"
HL_COLOR="\033[38;5;197m"
REG_COLOR="\033[0m"

if [ $ROON_DATABASE_DIR != "" ]; then
                ROON_LOG_FILE=$ROON_DEBUG_EXTERNAL_LOG
fi

## Log Function
echolog () {
    TIMESTAMP=$(date +%d.%m.%y-%H:%M:%S)
    if [[ $# == 2 ]]; then
        PARAMETER1=$1
        PARAMETER2=$2
        echo -e "${ST_COLOR}${TIMESTAMP}${REG_COLOR} --- ${HL_COLOR}${PARAMETER1}:${REG_COLOR} ${PARAMETER2}"
        echo "${TIMESTAMP} --- ${PARAMETER1}: ${PARAMETER2}" >> $ROON_LOG_FILE
    elif [[ $# == 1 ]]; then
        PARAMETER1=$1
        echo -e "${ST_COLOR}${TIMESTAMP}${REG_COLOR} --- ${PARAMETER1}"
        echo "${TIMESTAMP} --- ${PARAMETER1}" >> $ROON_LOG_FILE
    else
        echo -e "The echolog function requires 1 or 2 parameters."
    fi
}

if [[ $MAJOR_QTS_VER -ge 43 ]]; then
   BundledLibPath=false;
else
   BundledLibPath=true;
fi

if [ -f $ROON_PIDFILE ]; then
    PID=`cat "${ROON_PIDFILE}"`
fi

info ()
{
   ## Echoing System Info
   echolog "ROON_DATABASE_DIR" "${ROON_DATABASE_DIR}"
   echolog "ROON_DATABASE_DIR_FS" "${ROON_DATABASE_DIR_FS}"
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
   echolog "Loading additional 64-bit libs" "${BundledLibPath}"
}

start_RoonServer () {
  if [ "${ROON_DATABASE_DIR}" != "" ] && [ -d "${ROON_DATABASE_DIR}" ]; then
      export ROON_DATAROOT="$ROON_DATABASE_DIR"
      if $BundledLibPath; then
         export LD_LIBRARY_PATH="${ROON_LIB_DIR}:${LD_LIBRARY_PATH}"
      fi
      export ROON_INSTALL_TMPDIR="${ROON_TMP_DIR}"
      export ALSA_CONFIG_PATH
      export TMP="${ROON_TMP_DIR}"

      # Checking for additional start arguments.
      if [[ -f $ROON_DATABASE_DIR/ROON_DEBUG_LAUNCH_PARAMETERS.txt ]]; then
          ROON_ARGS=`cat "$ROON_DATABASE_DIR/ROON_DEBUG_LAUNCH_PARAMETERS.txt" | xargs | sed "s/ ---- /\n---- /g"`
      else
          ROON_ARGS=""
      fi
      echolog "ROON_DEBUG_ARGS" "${ROON_ARGS}"

      ## Start RoonServer
      setcfg ${QPKG_NAME} MULTIMEDIA_DISABLE_ON_START ${MULTIMEDIA_DISABLE} -f "${CONF}"
      ( ${QPKG_ROOT}/RoonServer/start.sh "${ROON_ARGS}" & echo $! >&3 ) 3>"${ROON_PIDFILE}"  | while read line; do echo `date +%d.%m.%y-%H:%M:%S` " --- $line"; done >> $ROON_LOG_FILE  2>&1 &
      echolog "RoonServer PID" "`cat ${ROON_PIDFILE}`"

      echo "" | tee -a $ROON_LOG_FILE
      echo "" | tee -a $ROON_LOG_FILE
      echo "########## Installed RoonServer Version ##########" | tee -a $ROON_LOG_FILE
      echo "${ROON_VERSION}" | tee -a $ROON_LOG_FILE
      echo "##################################################" | tee -a $ROON_LOG_FILE
      echo "" | tee -a $ROON_LOG_FILE
      echo "" | tee -a $ROON_LOG_FILE

      /sbin/write_log "[RoonServer] ROON_UPDATE_TMP_DIR = ${ROON_TMP_DIR}" 4
      /sbin/write_log "[RoonServer] ROON_DATABASE_DIR = ${ROON_DATABASE_DIR}" 4
      if $BundledLibPath; then
         /sbin/write_log "[RoonServer] QTS Version = ${QTS_VER}. Additional library folder = ${ROON_LIB_DIR}" 4
      else
         /sbin/write_log "[RoonServer] QTS Version = ${QTS_VER}. No additional libraries required." 4
      fi
      /sbin/write_log "[RoonServer] PID = `cat ${ROON_PIDFILE}`" 4
      /sbin/write_log "[RoonServer] Additional Arguments = ${ROON_ARGS}" 4
  else
      rm "${ROON_PIDFILE}"
      /sbin/write_log "[RoonServer] A storage location for RoonServer's database has not been set. Please create it in the web user interface in ordner to start RoonServer." 4
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
    if [ "$ENABLED" != "TRUE" ]; then
        echolog "$QPKG_NAME is disabled."
        exit 1
    fi

    if [ -f "$ROON_PIDFILE" ]; then
        if kill -s 0 $PID; then
            echolog "${QPKG_NAME} is already running with PID: $PID"
        else
            echo "" > $ROON_LOG_FILE
            echolog "INFO: Roon Server has previously not been stopped properly."
            /sbin/write_log "[${QPKG_NAME}] Roon Server has previously not been stopped properly." 2
            echolog "Starting ${QPKG_NAME} ..."
            start_daemon
        fi
    else
        echo "" > $ROON_LOG_FILE
        echolog "Starting ${QPKG_NAME} ..."
        start_daemon
    fi
    ;;

  stop)
    if [ -f "$ROON_PIDFILE" ]; then
        echolog "Stopping RoonServer..."
        echolog "Roon PID to be killed" "$PID"
        kill ${PID} >> $ROON_LOG_FILE
        rm "${ROON_PIDFILE}"
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
    $0 stop
    $0 start
    ;;

  *)
    echo "Usage: $0 {start|stop|restart}"
    exit 1
esac

exit 0
