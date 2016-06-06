#!/bin/sh
CONF=/etc/config/qpkg.conf
QPKG_NAME="RoonServer"
QPKG_ROOT=`/sbin/getcfg $QPKG_NAME Install_Path -f ${CONF}`
ROON_LIB_DIR="${QPKG_ROOT}/lib64"
ROON_TMP_DIR="${QPKG_ROOT}/tmp"
ROON_PIDFILE="${QPKG_ROOT}/RoonServer.pid"
ROON_DATAROOT=`( /sbin/getcfg $QPKG_NAME path -f /etc/config/smb.conf )`

if [ -f $ROON_PIDFILE ]; then
    PID=`( cat "${ROON_PIDFILE}" )`
fi

start_daemon ()
{			
        #Launch the service in the background if RoonServer share exists.
        if [ "${ROON_DATAROOT}" != "" ]; then
            export ROON_DATAROOT="$ROON_DATAROOT"
            export LD_LIBRARY_PATH="${ROON_LIB_DIR}"
            export ROON_INSTALL_TMPDIR="${ROON_TMP_DIR}"
            ${QPKG_ROOT}/RoonServer/start.sh &
            echo $! > "${ROON_PIDFILE}"
            /sbin/write_log "[RoonServer] ROON_UPDATE_TMP_DIR = ${ROON_TMP_DIR}" 4
            /sbin/write_log "[RoonServer] ROON_DATAROOT = ${ROON_DATAROOT}" 4
            /sbin/write_log "[RoonServer] Additional library folder = ${ROON_LIB_DIR}" 4
            /sbin/write_log "[RoonServer] PID = `( cat ${ROON_PIDFILE} )`" 4
        else
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
