#!/bin/sh
CONF=/etc/config/qpkg.conf
QPKG_NAME="RoonServer"
QPKG_ROOT=`/sbin/getcfg $QPKG_NAME Install_Path -f ${CONF}`
QTS_VER=`cat /etc/os-release | grep "VERSION_ID" | sed s/VERSION_ID=*// | tr -d '"'`
ROON_LIB_DIR="${QPKG_ROOT}/lib64"
ROON_TMP_DIR="${QPKG_ROOT}/tmp"
ROON_PIDFILE="${QPKG_ROOT}/RoonServer.pid"
ROON_ARG="${@:2}"
ROON_DATAROOT=`/sbin/getcfg $QPKG_NAME path -f /etc/config/smb.conf`
ALSA_CONFIG_PATH="${QPKG_ROOT}/etc/alsa/alsa.conf"

echo "${QPKG_ROOT}"
echo "${ROON_DATAROOT}"
OLD_IFS="$IFS"
IFS="."
QTS_VER_ARRAY=( $QTS_VER )
IFS="$OLD_IFS"

EXPORT_LIB_BELOW=(4 3)
DIGITCOUNT=0;
for i in "${EXPORT_LIB_BELOW[@]}"
do
   :
   if [ ${EXPORT_LIB_BELOW[$DIGITCOUNT]} -ge ${QTS_VER_ARRAY[$DIGITCOUNT]} ]; then
      echo "Bundled 64-Bit libraries do not need to be loaded!"
      BundledLibPath=false;
   else
      BundledLibPath=true;
      echo "Bundled 64-Bit libraries need to be loaded!"
      break
   fi
   DIGITCOUNT=$(($DIGITCOUNT+1));
done

if [ -f $ROON_PIDFILE ]; then
    PID=`cat "${ROON_PIDFILE}"`
fi

start_daemon ()
{			
        #Launch the service in the background if RoonServer share exists.
        if  "${ROON_DATAROOT}"; then
            export ROON_DATAROOT="$ROON_DATAROOT"
            if $BundledLibPath; then
               export LD_LIBRARY_PATH="${ROON_LIB_DIR}:${LD_LIBRARY_PATH}"
            fi
            export ROON_INSTALL_TMPDIR="${ROON_TMP_DIR}"
            export ALSA_CONFIG_PATH
            export TMP="${ROON_TMP_DIR}"
            ${QPKG_ROOT}/RoonServer/start.sh "${ROON_ARG}" &
            echo $! > "${ROON_PIDFILE}"
            /sbin/write_log "[RoonServer] ROON_UPDATE_TMP_DIR = ${ROON_TMP_DIR}" 4
            /sbin/write_log "[RoonServer] ROON_DATAROOT = ${ROON_DATAROOT}" 4
            /sbin/write_log "[RoonServer] Additional library folder = ${ROON_LIB_DIR}" 4
            /sbin/write_log "[RoonServer] PID = `cat ${ROON_PIDFILE}`" 4
            /sbin/write_log "[RoonServer] Additional Arguments = ${ROON_ARG}" 4
        else
            /sbin/setcfg "${QPKG_NAME}" Enable FALSE -f "${CONF}"
            rm "${ROON_PIDFILE}"
            /sbin/write_log "[RoonServer] Shared folder \"RoonServer\" could not be found. Please create it in the QTS before launching the package." 1
        fi
}

case "$1" in
  start)
    ENABLED=$(/sbin/getcfg $QPKG_NAME Enable -u -d FALSE -f $CONF)
    if [ "$ENABLED" != "TRUE" ]; then
        echo "$QPKG_NAME is disabled."
        exit 1
    fi

    if [ -f "$ROON_PIDFILE" ]; then
        if kill -s 0 $PID; then
            echo ${QPKG_NAME} is already running with PID: $PID
        else
            echo "INFO: Roon Server has previously not been stopped properly."
            /sbin/write_log "[RoonServer] Roon Server has previously not been stopped properly." 2
            echo "Starting ${QPKG_NAME} ..."
            start_daemon
        fi
    else
        echo "Starting ${QPKG_NAME} ..."
        start_daemon
    fi
    ;;

  stop)
    if [ -f "$ROON_PIDFILE" ]; then
        kill ${PID}
        rm "${ROON_PIDFILE}"
        rm -rf "${ROON_TMP_DIR}"/*
    else
        echo "${QPKG_NAME} is not running."
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
