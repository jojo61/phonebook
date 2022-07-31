<?php
/*
;     config.php user configuration file (ini-Format)
;     See installation manual and http://pbx-manager.de for details.
;     (C) 2006-2011 GEOTEK GmbH, Berlin - http://geotek.de
;     For security reasons, do NOT touch the starting and trailing characters!
;     Please note: 'yes', 'on' and '1' are synonymous, as are: 'no', 'off' and ''
;

[asterisk]

;     IP adress of your asterisk box, usually localhost
manager_ip=127.0.0.1

;     Asterisk management port, usually 5038
manager_port=5038

;     Asterisk management username. Default: manager
manager_username=admin

;     Asterisk managerment password. Default: insecure

manager_secret=mysecret

;     Timeout (ms) to pick up phone when dialing out
dial_timeout=20000

;     outgoing calls made from dialer are placed into this context
;     from-inside   (pbx-manager)
;     from-internal (Asterisk@home)
;     default       (Asterisk default)
dial_context=from-internal


;     This channel string is used by the dialer to initiate outgoing calls. Phonebook will add
;     your phone extension (e.g. 100) and builds the Asterisk Dial string (e.g. SIP/100) to let
;     your phone ring.
;     This needs to be changed only if your internal phones are not using the SIP protocol.
dial_channel_prefix=PJSIP/
;dial_channel_prefix=IAX/

;     variables set during click-to-dial as SIPADDHEADER
;     if not set then "Call-Info: <sip:nosip>\;answer-after=0" is used
;dial_variables="ALERT_INFO=info=alert-autoanswer"
;dial_variables="ALERT_INFO=answer-after=0"


*/
?>

