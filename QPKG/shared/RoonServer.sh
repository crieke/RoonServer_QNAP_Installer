#!/bin/sh
CONF=/etc/config/qpkg.conf
QPKG_NAME="RoonServer"
QPKG_ROOT=`/sbin/getcfg $QPKG_NAME Install_Path -f ${CONF}`
ROON_LIB_DIR="${QPKG_ROOT}/lib64"
ROON_TMP_DIR="${QPKG_ROOT}/tmp"
ROON_PIDFILE="${QPKG_ROOT}/RoonServer.pid"


start_daemon ()
{

        # Get the location of RoonServer Share
        ROON_DATAROOT=`( /sbin/getcfg $QPKG_NAME path -f /etc/config/smb.conf )`
		echo ${ROON_DATAROOT}
		
        # Launch the service in the background if RoonServer share exists.
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
        echo ${QPKG_NAME} is already running
    else
        echo Starting ${QPKG_NAME} ...
        start_daemon
    fi
    ;;

  stop)
    kill `( cat ${ROON_PIDFILE} )`
    rm "${ROON_PIDFILE}"
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
