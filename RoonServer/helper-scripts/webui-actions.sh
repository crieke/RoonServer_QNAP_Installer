#!/bin/sh

## VARIABLES
APP_NAME="RoonServer"
APKG_PKG_DIR="/usr/local/AppCentral/RoonServer"

ROON_FILENAME="RoonServer_linuxx64.tar.bz2"
ROON_PKG_URL="https://download.roonlabs.com/builds/$ROON_FILENAME"
WEBUI_STATUS="$APKG_PKG_DIR/web-status"
LOCKFILE="${APKG_PKG_DIR}/.webui.lock"
ROON_TMP_DIR="${APKG_PKG_DIR}/tmp"
ROON_WWW_DIR="/usr/local/www/RoonServer"
ROON_PIDFILE="${APKG_PKG_DIR}/RoonServer.pid"
ROON_LOG_FILE="${APKG_PKG_DIR}/RoonServer.log"

if [ -f $LOCKFILE ]; then
    exit
fi

lockfile() {
    case $1 in
        create)
            if [ ! -f $LOCKFILE ]; then
            echo "Creating Lockfile."
                touch "${LOCKFILE}"
            else
                echo "WebUI_Helper-Process is already running..."
                exit 1
            fi
        ;;
        remove)
            echo "Removing Lockfile."
            rm -f "${LOCKFILE}"
        ;;
        *)
            echo "Usage: $0 {create|remove|check}"
    esac 
}

lockfile create

getInfo () {
    # Getting system info for debugging purpose
    ADM_VER=`confutil -get /etc/nas.conf Basic Version`
    ARCH=$(uname -m)
    MODEL=`confutil -get /etc/nas.conf Basic Model`
    NAS_SERIAL=`confutil -get /etc/nas.conf Basic SerialNumber`
    NAS_MEMTOTAL=`awk '/MemTotal/ {print $2}' /proc/meminfo`
    NAS_MEMFREE=`awk '/MemFree/ {print $2}' /proc/meminfo`
    APP_VERSION=$(cat ${APKG_PKG_DIR}/CONTROL/config.json | grep "version" | tr \" " " |  awk '{print $3}')
    ROON_VERSION=`cat "${APKG_PKG_DIR}/RoonServer/VERSION"`
    ROON_TMP_DIR="${APKG_PKG_DIR}/tmp"
    ROON_PIDFILE="${APKG_PKG_DIR}/RoonServer.pid"
    WATCH_PID="$(cat ${APKG_PKG_DIR}/webactions.pid)"
    ROON_WEBACTIONS_PIDFILE="${APKG_PKG_DIR}/webactions.pid"
    ROON_DATABASE_DIR=`awk -F "= " '/DB_Path/ {print $2}' ${APKG_PKG_DIR}/etc/RoonServer.conf`
    NAS_DEF_IF=$(route | grep default | awk '{print $8}')
    NAS_IF_MTU=$(cat /sys/class/net/${NAS_DEF_IF}/mtu)
    NAS_HOSTNAME=`confutil -get /etc/nas.conf Basic Hostname`
    ROON_DATABASE_DIR_FS=`df -T "${ROON_DATABASE_DIR}" | grep "^/dev" | awk '{print $2}'`
    ROON_LOG_FILE="${APKG_PKG_DIR}/RoonServer.log"
    ROON_DEBUG_EXTERNAL_LOG="${ROON_DATABASE_DIR}/ROON_DEBUG_EXTERNAL_LOG.txt"

    # Hidden feature to write a logfile into the database location (only if the txt file exists)
    if [[ -f $ROON_DEBUG_EXTERNAL_LOG ]]; then
	    ROON_LOG_FILE=$ROON_DEBUG_EXTERNAL_LOG
    fi

    if [ -f $ROON_PIDFILE ]; then
	    START_PID=`cat "${ROON_PIDFILE}"`
    fi
}

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

showInfo ()
{
   ## Echoing System Info
   echolog "ROON_DATABASE_DIR" "${ROON_DATABASE_DIR}"
   echolog "ROON_DATABASE_DIR_FS" "${ROON_DATABASE_DIR_FS}"
   echolog "ROON_APP_DIR" "${APKG_PKG_DIR}"
   echolog "Model" "${MODEL}"
   echolog "Asustor Serial" "${NAS_SERIAL}"
   echolog "Architecture" "${ARCH}"
   echolog "Total Memory" "${NAS_MEMTOTAL}"
   echolog "Available Memory" "${NAS_MEMFREE}"
   echolog "ADM Version" "${ADM_VER}"
   echolog "PKG Version" "${APP_VERSION}"
   echolog "Hostname" "${NAS_HOSTNAME}"
   echolog "Default Interface" "${NAS_DEF_IF}"
   echolog "MTU" "${NAS_IF_MTU}"
   echolog "Watch PID" "${WATCH_PID}"
}

startRoonServer() {
    echo "start" > $LOCKFILE
    getInfo
    if [ -f "$ROON_PIDFILE" ]; then
        echolog "PID-File exists."
        if [ -d "/proc/${START_PID}" ]; then
            echolog "RoonServer already running."
            return 1
        fi
    fi

    echolog "Starting RoonServer"
    showInfo
    if [ "$ROON_DATABASE_DIR" != "" ] && [ -d "$ROON_DATABASE_DIR" ]; then
        export ROON_DATAROOT="${ROON_DATABASE_DIR}"
	    export ROON_INSTALL_TMPDIR="${ROON_TMP_DIR}"
	    export TMP="${ROON_TMP_DIR}"

        # Checking for additional start arguments.
	    if [[ -f "{$ROON_DATABASE_DIR}/ROON_DEBUG_LAUNCH_PARAMETERS.txt" ]]; then
	        ROON_ARGS=`cat "$ROON_DATABASE_DIR/ROON_DEBUG_LAUNCH_PARAMETERS.txt" | xargs | sed "s/ ---- /\n---- /g"`
        else
	        ROON_ARGS=""
	    fi
	    echolog "ROON_DEBUG_ARGS ${ROON_ARGS}"

        ## Start RoonServer
	    ( ${APKG_PKG_DIR}/RoonServer/start.sh "${ROON_ARGS}" & echo $! >&3 ) 3>"${ROON_PIDFILE}"  | while read line; do echo `date +%d.%m.%y-%H:%M:%S` " --- $line"; done >> $ROON_LOG_FILE  2>&1 &
	    echolog "RoonServer PID `cat ${ROON_PIDFILE}`"

	    echo "" | tee -a $ROON_LOG_FILE
	    echo "" | tee -a $ROON_LOG_FILE
	    echo "########## Installed RoonServer Version ##########" | tee -a $ROON_LOG_FILE
	    echo "${ROON_VERSION}" | tee -a $ROON_LOG_FILE
	    echo "##################################################" | tee -a $ROON_LOG_FILE
	    echo "" | tee -a $ROON_LOG_FILE
	    echo "" | tee -a $ROON_LOG_FILE
   	else
		echolog "Database path not set in web ui."
    fi
}

stopRoonServer() {
    echolog "Stopping RoonServer."
    echo "stop" > $LOCKFILE
    # stop script here
	if [ -f "$ROON_PIDFILE" ]; then
	    echolog "Roon PID to be killed: $START_PID" | tee -a $ROON_LOG_FILE
		kill ${START_PID} >> $ROON_LOG_FILE
		rm "${ROON_PIDFILE}" >> $ROON_LOG_FILE
	fi
}

logs() {
    logDate=$1
    getInfo
    #removing previous logfile (if exists)

    echolog "Creating Log-zipfile."
    echo "logs" > $LOCKFILE
	start_dir=$(pwd)
    zipFile="${ROON_WWW_DIR}/tmp/RoonServer_Asustor_Logs_$logDate.zip"
    cd $ROON_DATABASE_DIR
    
    if [ -d "$ROON_DATABASE_DIR/RoonServer" ]; then
        echolog "Adding RoonServer/Logs"
        7z a $zipFile RoonServer/Logs
    fi

    if [ -d "$ROON_DATABASE_DIR/RAATServer" ]; then
        echolog "Adding RAATServer/Logs"
        7z a $zipFile RAATServer/Logs
    fi

    cd $start_dir

    echolog "Adding stdout logfile"
    7z a $zipFile $ROON_LOG_FILE
}

downloadBinaries() {
    echo "download" > $LOCKFILE
    if [ -f "$ROON_DATABASE_DIR/ROON_DEBUG_INSTALL_URL.txt" ]; then
        CUSTOM_INSTALL_URL=`/bin/cat "$ROON_DATABASE_DIR/ROON_DEBUG_INSTALL_URL.txt"`
        if [ ${CUSTOM_INSTALL_URL:0:4} == "http" ] && [ $(basename ${CUSTOM_INSTALL_URL}) == $(basename ${ROON_PKG_URL}) ] ; then
            ROON_PKG_URL="${CUSTOM_INSTALL_URL}"
        fi
    fi

    cd "$APKG_PKG_DIR/tmp"
    /usr/bin/wget "$ROON_PKG_URL"
    /bin/tar xjf "$ROON_FILENAME" -C "$APKG_PKG_DIR/tmp"
    mv "$APKG_PKG_DIR/RoonServer" "$APKG_PKG_DIR/RoonServer_Old"
    mv "$APKG_PKG_DIR/tmp/RoonServer" "$APKG_PKG_DIR/."
    /bin/rm "$ROON_FILENAME"
    /bin/rm -R "$APKG_PKG_DIR/RoonServer_Old"
    getInfo
}

echo `cat "$APKG_PKG_DIR/web-status"`

#check if RoonServer has initially been downloaded after apkg install
if [ ! -d "$APKG_PKG_DIR/RoonServer" ]; then
    getInfo
	echolog "Downloading RoonServer"
	downloadBinaries
fi

#check web ui status
if [ -f "$WEBUI_STATUS" ]; then
    getInfo
    WEBUI_ACTION=`cat "$APKG_PKG_DIR/web-status"`
    echolog "Performing Action: $WEBUI_ACTION"
    set -- $WEBUI_ACTION
    rm $WEBUI_STATUS
        case $1 in
            start)
                startRoonServer
                ;;
            restart)
                echolog "Restarting RoonServer"
                stopRoonServer
                wait 2
                startRoonServer
                ;;
            redownload)
                stopRoonServer
                downloadBinaries
                startRoonServer
                ;;
            logs)
                logs $2
                ;;

            *)
                echo "Illegal action."
                ;;
        esac
fi

lockfile remove
