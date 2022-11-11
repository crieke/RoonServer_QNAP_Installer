#!/bin/sh
## VARIABLES
CONF=/etc/config/qpkg.conf
QPKG_NAME="RoonServer"
QPKG_ROOT=`/sbin/getcfg $QPKG_NAME Install_Path -f ${CONF}`
ROON_PKG_URL="https://download.roonlabs.net/builds/RoonServer_linuxx64.tar.bz2"
ROON_ARCHIVE="${ROON_PKG_URL##*/}"
ROON_DATABASE_DIR=`/sbin/getcfg "${QPKG_NAME}" DB_Path -f ${CONF}`
lockfile="${QPKG_ROOT}/.helperscript.lock"
CONFIRMATION="NO"

lockfile() {
    case $1 in
        create)
            echo "Checking if this is the only running Roon-QNAP-Helper-Process..."
            if [ ! -f $lockfile ]; then
                echo "Creating lock file..."
                touch "${lockfile}"
                echo "This is the only Roon-QNAP-Helper-Process..."
            else
                echo "Roon-QNAP_Helper-Process is already running..."
                echo "Exiting.."
                exit 1
            fi
        ;;
        remove)
            echo "Removing lock file..."
            rm -f "${lockfile}"   
        ;;
        *)
            echo "Usage: $0 {create|remove|check}"
    esac
}


installRoon() {
    echo "Checking Download-URL..."
    ## Check if alternate binary URL is set
    if [[ -f "${ROON_DATABASE_DIR}/ROON_DEBUG_INSTALL_URL.txt" ]]; then
        CUSTOM_INSTALL_URL=`cat "${ROON_DATABASE_DIR}/ROON_DEBUG_INSTALL_URL.txt"`
        if [[ ${CUSTOM_INSTALL_URL:0:4} == "http" ]]  && [[ $(basename ${CUSTOM_INSTALL_URL}) == $(basename ${ROON_PKG_URL}) ]]; then
            ROON_PKG_URL="${CUSTOM_INSTALL_URL}"
            echo "<b>Using custom download URL.</b>"
        fi
    else
       echo "Using default download location."
    fi

    ## Download installation archive
${QPKG_ROOT}/RoonServer.sh stop keepwebalive > /dev/null 2>&1
    echo "tmp storage: ${QPKG_ROOT}/tmp/"
    echo "Downloading file: ${ROON_PKG_URL}..."
    wget -NS -P "${QPKG_ROOT}/tmp/" "${ROON_PKG_URL}"
    DL_STATUS="$(wget -NSP "${QPKG_ROOT}/tmp/" "${ROON_PKG_URL}" 2>&1 | grep 'HTTP/' | awk '{print $2}')"
    R=$?
    echo "Download Status: $DL_STATUS"
    if [ $DL_STATUS -eq 200 ]; then
        echo "Removing previous RoonServer..."
        rm -R "${QPKG_ROOT}/RoonServer"
        echo "Extracting .tar.bz2 file..."
        tar xjf "${QPKG_ROOT}/tmp/${ROON_ARCHIVE}" -C "${QPKG_ROOT}"
        R=$?
    fi
    echo "Deleting downloaded .tar.bz2 file..."
    rm -f "${QPKG_ROOT}"/tmp/"${ROON_ARCHIVE}"
    if [ $DL_STATUS -ne 200 ]; then
        echo "Could not download installation archive from Roon Labs website. Please check your internet connection."
    fi
    /sbin/qpkg_service start RoonServer  > /dev/null 2>&1
}

case "$1" in
    reinstall)
        lockfile create
        installRoon
        lockfile remove
        echo "<span style=\"color:#468847;\"><b>Everything done.</b></span>"
    ;;
    *)
        echo "Usage: $0 {reinstall|restart}"
        exit 1
    ;;
esac
