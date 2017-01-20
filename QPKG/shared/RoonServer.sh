#!/bin/sh
CONF=/etc/config/qpkg.conf
QPKG_NAME="RoonServer"
QPKG_ROOT=`/sbin/getcfg $QPKG_NAME Install_Path -f ${CONF}`
QTS_VER=`/sbin/getcfg system version`
QPKG_VERSION=`/sbin/getcfg $QPKG_NAME Version -f ${CONF}`
MAJOR_QTS_VER=`echo "$QTS_VER" | tr -d '.' | cut -c1-2`
QNAP_SERIAL=`get_hwsn`
ROON_VERSION=`cat "${QPKG_ROOT}/RoonServer/VERSION"`
ROON_LIB_DIR="${QPKG_ROOT}/lib64"
ROON_TMP_DIR="${QPKG_ROOT}/tmp"
ROON_PIDFILE="${QPKG_ROOT}/RoonServer.pid"
ROON_DATAROOT=`/sbin/getcfg $QPKG_NAME path -f /etc/config/smb.conf`
ALSA_CONFIG_PATH="${QPKG_ROOT}/etc/alsa/alsa.conf"
WATCH_SHARE_PID="${QPKG_ROOT}/share_watchdog.pid"

## Echoing System Info
echo "QPKG_ROOT: ${QPKG_ROOT}"
echo "ROON_DATAROOT: ${ROON_DATAROOT}"
echo "QTS-Version: ${QTS_VER} (Compare Int: ${MAJOR_QTS_VER})"
echo "RoonServer .qpkg Version: "${QPKG_VERSION}

echo ""; echo "";echo "########## Installed RoonServer Version ##########"
echo "${ROON_VERSION}"
echo "##################################################"; echo ""; echo ""

if [[ $MAJOR_QTS_VER -ge 43 ]]; then
   BundledLibPath=false;
   echo "No additional libraries required."
else
   BundledLibPath=true;
   echo "Additional libraries will be loaded."
fi

if [ -f $ROON_PIDFILE ]; then
    PID=`cat "${ROON_PIDFILE}"`
fi

if [ -f $WATCH_SHARE_PID ]; then
    PID2=`cat "${WATCH_SHARE_PID}"`
fi

start_daemon ()
{			
        #Launch the service in the background if RoonServer share exists.
        if [ "${ROON_DATAROOT}" != "" ]; then
            export ROON_DATAROOT="$ROON_DATAROOT"
            if $BundledLibPath; then
               export LD_LIBRARY_PATH="${ROON_LIB_DIR}:${LD_LIBRARY_PATH}"
            fi
            export ROON_INSTALL_TMPDIR="${ROON_TMP_DIR}"
            export ALSA_CONFIG_PATH
            export TMP="${ROON_TMP_DIR}"
            export ROON_FILEBROWSER_IGNORE_ALL_MOUNTS=1
            export ROON_FILEBROWSER_VIRTUAL_MOUNT1="${QNAP_SERIAL}:QNAP $(getsysinfo model):$(/bin/hostname), ${QNAP_SERIAL}, QTS ${QTS_VER}:${QPKG_ROOT}/mnt"

            #Watch /share folders for symlink changes, and add or delete them in Roon's mnt folder.
            ## Start Watchdog for RoonServer "mnt" directory
            setsid ${QPKG_ROOT}/share_watchdog.sh &
            PID2=$!
            echo $PID2 > "${WATCH_SHARE_PID}"    
            echo "WatchShare PID: " $PID2

            # Checking for additional start arguments.
            if [[ -f $ROON_DATAROOT/ROON_DEBUG_LAUNCH_PARAMETERS.txt ]]; then
                ROON_ARGS=`cat "$ROON_DATAROOT/ROON_DEBUG_LAUNCH_PARAMETERS.txt" | xargs | sed "s/ ---- /\n---- /g"`
            else
                ROON_ARGS=""
            fi
            echo "RoonServer Arguments: ${ROON_ARGS}"

            ## Start RoonServer
            ${QPKG_ROOT}/RoonServer/start.sh "${ROON_ARGS}" &
            echo $! > "${ROON_PIDFILE}"
            /sbin/write_log "[RoonServer] ROON_UPDATE_TMP_DIR = ${ROON_TMP_DIR}" 4
            /sbin/write_log "[RoonServer] ROON_DATAROOT = ${ROON_DATAROOT}" 4
            if $BundledLibPath; then
               /sbin/write_log "[RoonServer] QTS Version = ${QTS_VER}. Additional library folder = ${ROON_LIB_DIR}" 4
            else
               /sbin/write_log "[RoonServer] QTS Version = ${QTS_VER}. No additional libraries required." 4
            fi
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
        kill -- -`cat $WATCH_SHARE_PID`
        rm "${WATCH_SHARE_PID}"
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
