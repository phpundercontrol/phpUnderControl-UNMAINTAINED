#!/bin/sh
# Original script taken from http://felix.phpbelgium.be/blog/2009/02/07/setting-up-phpundercontrol/

PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:
. /lib/lsb/init-functions

CC_INSTALL_DIR=%cc-install-dir%
CC_BIN=%cc-bin%
JAVA_HOME=%java-home%
NAME=cruisecontrol
DAEMON=${CC_INSTALL_DIR}/${CC_BIN}
PIDFILE=${CC_INSTALL_DIR}/cc.pid

test -x $DAEMON || (log_failure_msg "Cruisecontrol server not found" && exit 5)

RUNASUSER=%run-as-user%
UGID=$(getent passwd $RUNASUSER | cut -f 3,4 -d:) || true

case $1 in
start)
    log_daemon_msg "Starting Cruisecontrol server..."
    if [ -z "$UGID" ]; then
        log_failure_msg "user \"$RUNASUSER\" does not exist"
    exit 1
    fi
    cd ${CC_INSTALL_DIR}
    ./${CC_BIN} > /dev/null 2>&1
    log_end_msg $?
;;

stop)
    log_daemon_msg "Stopping Cruisecontrol server..."
    start-stop-daemon --stop --quiet --oknodo --pidfile $PIDFILE
    log_end_msg $?
    rm -f $PIDFILE
    ;;

restart|force-reload)
    $0 stop && sleep 2 && $0 start
;;

status)
    pidofproc -p $PIDFILE $DAEMON >/dev/null
    status=$?
    if [ $status -eq 0 ]; then
        log_success_msg "Cruisecontrol server is running."
    else
        log_failure_msg "Cruisecontrol server is not running."
    fi
    exit $status
;;

*)
    echo "Usage: $0 {start|stop|restart|force-reload|status}"
    exit 2
;;
esac