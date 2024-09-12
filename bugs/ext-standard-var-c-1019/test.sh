#!/bin/sh

export TEST_PHP_SRCDIR='/home/phpfuzz/WorkSpace/flowfusion/php-src'
export CC='clang-15'
export TEST_PHP_EXECUTABLE='/home/phpfuzz/WorkSpace/flowfusion/php-src/sapi/cli/php'
export SHELL='/bin/sh'
export TERM_PROGRAM_VERSION='3.2a'
export TMUX='/tmp/tmux-1000/default,262,0'
export HOSTNAME='ce1ee0c92a8c'
export PWD='/home/phpfuzz/WorkSpace/flowfusion/php-src'
export MAKEOVERRIDES='${-*-command-variables-*-}'
export HOME='/home/phpfuzz'
export LS_COLORS='rs=0:di=01;34:ln=01;36:mh=00:pi=40;33:so=01;35:do=01;35:bd=40;33;01:cd=40;33;01:or=40;31;01:mi=00:su=37;41:sg=30;43:ca=30;41:tw=30;42:ow=34;42:st=37;44:ex=01;32:*.tar=01;31:*.tgz=01;31:*.arc=01;31:*.arj=01;31:*.taz=01;31:*.lha=01;31:*.lz4=01;31:*.lzh=01;31:*.lzma=01;31:*.tlz=01;31:*.txz=01;31:*.tzo=01;31:*.t7z=01;31:*.zip=01;31:*.z=01;31:*.dz=01;31:*.gz=01;31:*.lrz=01;31:*.lz=01;31:*.lzo=01;31:*.xz=01;31:*.zst=01;31:*.tzst=01;31:*.bz2=01;31:*.bz=01;31:*.tbz=01;31:*.tbz2=01;31:*.tz=01;31:*.deb=01;31:*.rpm=01;31:*.jar=01;31:*.war=01;31:*.ear=01;31:*.sar=01;31:*.rar=01;31:*.alz=01;31:*.ace=01;31:*.zoo=01;31:*.cpio=01;31:*.7z=01;31:*.rz=01;31:*.cab=01;31:*.wim=01;31:*.swm=01;31:*.dwm=01;31:*.esd=01;31:*.jpg=01;35:*.jpeg=01;35:*.mjpg=01;35:*.mjpeg=01;35:*.gif=01;35:*.bmp=01;35:*.pbm=01;35:*.pgm=01;35:*.ppm=01;35:*.tga=01;35:*.xbm=01;35:*.xpm=01;35:*.tif=01;35:*.tiff=01;35:*.png=01;35:*.svg=01;35:*.svgz=01;35:*.mng=01;35:*.pcx=01;35:*.mov=01;35:*.mpg=01;35:*.mpeg=01;35:*.m2v=01;35:*.mkv=01;35:*.webm=01;35:*.webp=01;35:*.ogm=01;35:*.mp4=01;35:*.m4v=01;35:*.mp4v=01;35:*.vob=01;35:*.qt=01;35:*.nuv=01;35:*.wmv=01;35:*.asf=01;35:*.rm=01;35:*.rmvb=01;35:*.flc=01;35:*.avi=01;35:*.fli=01;35:*.flv=01;35:*.gl=01;35:*.dl=01;35:*.xcf=01;35:*.xwd=01;35:*.yuv=01;35:*.cgm=01;35:*.emf=01;35:*.ogv=01;35:*.ogx=01;35:*.aac=00;36:*.au=00;36:*.flac=00;36:*.m4a=00;36:*.mid=00;36:*.midi=00;36:*.mka=00;36:*.mp3=00;36:*.mpc=00;36:*.ogg=00;36:*.ra=00;36:*.wav=00;36:*.oga=00;36:*.opus=00;36:*.spx=00;36:*.xspf=00;36:'
export MFLAGS=''
export LESSCLOSE='/usr/bin/lesspipe %s %s'
export MAKEFLAGS=' -- TEST_PHP_ARGS=-j16'
export TERM='screen'
export LESSOPEN='| /usr/bin/lesspipe %s'
export TMUX_PANE='%0'
export MAKE_TERMERR='/dev/pts/2'
export SHLVL='3'
export MAKELEVEL='1'
export LC_CTYPE='C.UTF-8'
export PATH='/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin'
export OLDPWD='/home/phpfuzz/WorkSpace/flowfusion/php-src'
export TEST_PHP_ARGS='-j16'
export TERM_PROGRAM='tmux'
export _='/home/phpfuzz/WorkSpace/flowfusion/php-src/sapi/cli/php'
export TEMP='/tmp'
export SKIP_ONLINE_TESTS='1'
export TEST_PHP_EXECUTABLE_ESCAPED=''\''/home/phpfuzz/WorkSpace/flowfusion/php-src/sapi/cli/php'\'''
export TEST_PHP_CGI_EXECUTABLE='/home/phpfuzz/WorkSpace/flowfusion/php-src/sapi/cgi/php-cgi'
export TEST_PHP_CGI_EXECUTABLE_ESCAPED=''\''/home/phpfuzz/WorkSpace/flowfusion/php-src/sapi/cgi/php-cgi'\'''
export TEST_PHPDBG_EXECUTABLE='/home/phpfuzz/WorkSpace/flowfusion/php-src/sapi/phpdbg/phpdbg'
export TEST_PHPDBG_EXECUTABLE_ESCAPED=''\''/home/phpfuzz/WorkSpace/flowfusion/php-src/sapi/phpdbg/phpdbg'\'''
export REDIRECT_STATUS='1'
export QUERY_STRING=''
export PATH_TRANSLATED='/home/phpfuzz/WorkSpace/flowfusion/php-src/tests/fused/fused559.php'
export SCRIPT_FILENAME='/home/phpfuzz/WorkSpace/flowfusion/php-src/tests/fused/fused559.php'
export REQUEST_METHOD='GET'
export CONTENT_TYPE=''
export CONTENT_LENGTH=''
export TZ=''
export TEST_PHP_EXTRA_ARGS=' -n -c '\''/home/phpfuzz/WorkSpace/flowfusion/php-src/tmp-php.ini'\''  -d "opcache.cache_id=worker14" -d "output_handler=" -d "open_basedir=" -d "disable_functions=" -d "output_buffering=Off" -d "error_reporting=32767" -d "display_errors=1" -d "display_startup_errors=1" -d "log_errors=0" -d "html_errors=0" -d "track_errors=0" -d "report_memleaks=1" -d "report_zend_debug=0" -d "docref_root=" -d "docref_ext=.html" -d "error_prepend_string=" -d "error_append_string=" -d "auto_prepend_file=" -d "auto_append_file=" -d "ignore_repeated_errors=0" -d "precision=14" -d "serialize_precision=-1" -d "memory_limit=128M" -d "opcache.fast_shutdown=0" -d "opcache.file_update_protection=0" -d "opcache.revalidate_freq=0" -d "opcache.jit_hot_loop=1" -d "opcache.jit_hot_func=1" -d "opcache.jit_hot_return=1" -d "opcache.jit_hot_side_exit=1" -d "opcache.jit_max_root_traces=100000" -d "opcache.jit_max_side_traces=100000" -d "opcache.jit_max_exit_counters=100000" -d "opcache.protect_memory=1" -d "zend.assertions=1" -d "zend.exception_ignore_args=0" -d "zend.exception_string_param_max_len=15" -d "short_open_tag=0" -d "extension_dir=/home/phpfuzz/WorkSpace/flowfusion/php-src/modules/" -d "zend_extension=/home/phpfuzz/WorkSpace/flowfusion/php-src/modules/opcache.so" -d "session.auto_start=0" -d "zend.enable_gc=1" -d "opcache.enabled=1"'
export HTTP_COOKIE=''

case "$1" in
"gdb")
    gdb --args '/home/phpfuzz/WorkSpace/flowfusion/php-src/sapi/cli/php'  -n -c '/home/phpfuzz/WorkSpace/flowfusion/php-src/tmp-php.ini'   -d "opcache.cache_id=worker14" -d "output_handler=" -d "open_basedir=" -d "disable_functions=" -d "output_buffering=Off" -d "error_reporting=32767" -d "display_errors=1" -d "display_startup_errors=1" -d "log_errors=0" -d "html_errors=0" -d "track_errors=0" -d "report_memleaks=1" -d "report_zend_debug=0" -d "docref_root=" -d "docref_ext=.html" -d "error_prepend_string=" -d "error_append_string=" -d "auto_prepend_file=" -d "auto_append_file=" -d "ignore_repeated_errors=0" -d "precision=14" -d "serialize_precision=-1" -d "memory_limit=128M" -d "opcache.fast_shutdown=0" -d "opcache.file_update_protection=0" -d "opcache.revalidate_freq=0" -d "opcache.jit_hot_loop=1" -d "opcache.jit_hot_func=1" -d "opcache.jit_hot_return=1" -d "opcache.jit_hot_side_exit=1" -d "opcache.jit_max_root_traces=100000" -d "opcache.jit_max_side_traces=100000" -d "opcache.jit_max_exit_counters=100000" -d "opcache.protect_memory=1" -d "zend.assertions=1" -d "zend.exception_ignore_args=0" -d "zend.exception_string_param_max_len=15" -d "short_open_tag=0" -d "extension_dir=/home/phpfuzz/WorkSpace/flowfusion/php-src/modules/" -d "zend_extension=/home/phpfuzz/WorkSpace/flowfusion/php-src/modules/opcache.so" -d "session.auto_start=0" -d "zend.enable_gc=1" -d "opcache.enabled=1" -f "/home/phpfuzz/WorkSpace/flowfusion/php-src/tests/fused/fused559.php"  2>&1
    ;;
"lldb")
    lldb -- '/home/phpfuzz/WorkSpace/flowfusion/php-src/sapi/cli/php'  -n -c '/home/phpfuzz/WorkSpace/flowfusion/php-src/tmp-php.ini'   -d "opcache.cache_id=worker14" -d "output_handler=" -d "open_basedir=" -d "disable_functions=" -d "output_buffering=Off" -d "error_reporting=32767" -d "display_errors=1" -d "display_startup_errors=1" -d "log_errors=0" -d "html_errors=0" -d "track_errors=0" -d "report_memleaks=1" -d "report_zend_debug=0" -d "docref_root=" -d "docref_ext=.html" -d "error_prepend_string=" -d "error_append_string=" -d "auto_prepend_file=" -d "auto_append_file=" -d "ignore_repeated_errors=0" -d "precision=14" -d "serialize_precision=-1" -d "memory_limit=128M" -d "opcache.fast_shutdown=0" -d "opcache.file_update_protection=0" -d "opcache.revalidate_freq=0" -d "opcache.jit_hot_loop=1" -d "opcache.jit_hot_func=1" -d "opcache.jit_hot_return=1" -d "opcache.jit_hot_side_exit=1" -d "opcache.jit_max_root_traces=100000" -d "opcache.jit_max_side_traces=100000" -d "opcache.jit_max_exit_counters=100000" -d "opcache.protect_memory=1" -d "zend.assertions=1" -d "zend.exception_ignore_args=0" -d "zend.exception_string_param_max_len=15" -d "short_open_tag=0" -d "extension_dir=/home/phpfuzz/WorkSpace/flowfusion/php-src/modules/" -d "zend_extension=/home/phpfuzz/WorkSpace/flowfusion/php-src/modules/opcache.so" -d "session.auto_start=0" -d "zend.enable_gc=1" -d "opcache.enabled=1" -f "/home/phpfuzz/WorkSpace/flowfusion/php-src/tests/fused/fused559.php"  2>&1
    ;;
"valgrind")
    USE_ZEND_ALLOC=0 valgrind $2 '/home/phpfuzz/WorkSpace/flowfusion/php-src/sapi/cli/php'  -n -c '/home/phpfuzz/WorkSpace/flowfusion/php-src/tmp-php.ini'   -d "opcache.cache_id=worker14" -d "output_handler=" -d "open_basedir=" -d "disable_functions=" -d "output_buffering=Off" -d "error_reporting=32767" -d "display_errors=1" -d "display_startup_errors=1" -d "log_errors=0" -d "html_errors=0" -d "track_errors=0" -d "report_memleaks=1" -d "report_zend_debug=0" -d "docref_root=" -d "docref_ext=.html" -d "error_prepend_string=" -d "error_append_string=" -d "auto_prepend_file=" -d "auto_append_file=" -d "ignore_repeated_errors=0" -d "precision=14" -d "serialize_precision=-1" -d "memory_limit=128M" -d "opcache.fast_shutdown=0" -d "opcache.file_update_protection=0" -d "opcache.revalidate_freq=0" -d "opcache.jit_hot_loop=1" -d "opcache.jit_hot_func=1" -d "opcache.jit_hot_return=1" -d "opcache.jit_hot_side_exit=1" -d "opcache.jit_max_root_traces=100000" -d "opcache.jit_max_side_traces=100000" -d "opcache.jit_max_exit_counters=100000" -d "opcache.protect_memory=1" -d "zend.assertions=1" -d "zend.exception_ignore_args=0" -d "zend.exception_string_param_max_len=15" -d "short_open_tag=0" -d "extension_dir=/home/phpfuzz/WorkSpace/flowfusion/php-src/modules/" -d "zend_extension=/home/phpfuzz/WorkSpace/flowfusion/php-src/modules/opcache.so" -d "session.auto_start=0" -d "zend.enable_gc=1" -d "opcache.enabled=1" -f "/home/phpfuzz/WorkSpace/flowfusion/php-src/tests/fused/fused559.php"  2>&1
    ;;
"rr")
    rr record $2 '/home/phpfuzz/WorkSpace/flowfusion/php-src/sapi/cli/php'  -n -c '/home/phpfuzz/WorkSpace/flowfusion/php-src/tmp-php.ini'   -d "opcache.cache_id=worker14" -d "output_handler=" -d "open_basedir=" -d "disable_functions=" -d "output_buffering=Off" -d "error_reporting=32767" -d "display_errors=1" -d "display_startup_errors=1" -d "log_errors=0" -d "html_errors=0" -d "track_errors=0" -d "report_memleaks=1" -d "report_zend_debug=0" -d "docref_root=" -d "docref_ext=.html" -d "error_prepend_string=" -d "error_append_string=" -d "auto_prepend_file=" -d "auto_append_file=" -d "ignore_repeated_errors=0" -d "precision=14" -d "serialize_precision=-1" -d "memory_limit=128M" -d "opcache.fast_shutdown=0" -d "opcache.file_update_protection=0" -d "opcache.revalidate_freq=0" -d "opcache.jit_hot_loop=1" -d "opcache.jit_hot_func=1" -d "opcache.jit_hot_return=1" -d "opcache.jit_hot_side_exit=1" -d "opcache.jit_max_root_traces=100000" -d "opcache.jit_max_side_traces=100000" -d "opcache.jit_max_exit_counters=100000" -d "opcache.protect_memory=1" -d "zend.assertions=1" -d "zend.exception_ignore_args=0" -d "zend.exception_string_param_max_len=15" -d "short_open_tag=0" -d "extension_dir=/home/phpfuzz/WorkSpace/flowfusion/php-src/modules/" -d "zend_extension=/home/phpfuzz/WorkSpace/flowfusion/php-src/modules/opcache.so" -d "session.auto_start=0" -d "zend.enable_gc=1" -d "opcache.enabled=1" -f "/home/phpfuzz/WorkSpace/flowfusion/php-src/tests/fused/fused559.php"  2>&1
    ;;
*)
    '/home/phpfuzz/WorkSpace/flowfusion/php-src/sapi/cli/php'  -n -c '/home/phpfuzz/WorkSpace/flowfusion/php-src/tmp-php.ini'   -d "opcache.cache_id=worker14" -d "output_handler=" -d "open_basedir=" -d "disable_functions=" -d "output_buffering=Off" -d "error_reporting=32767" -d "display_errors=1" -d "display_startup_errors=1" -d "log_errors=0" -d "html_errors=0" -d "track_errors=0" -d "report_memleaks=1" -d "report_zend_debug=0" -d "docref_root=" -d "docref_ext=.html" -d "error_prepend_string=" -d "error_append_string=" -d "auto_prepend_file=" -d "auto_append_file=" -d "ignore_repeated_errors=0" -d "precision=14" -d "serialize_precision=-1" -d "memory_limit=128M" -d "opcache.fast_shutdown=0" -d "opcache.file_update_protection=0" -d "opcache.revalidate_freq=0" -d "opcache.jit_hot_loop=1" -d "opcache.jit_hot_func=1" -d "opcache.jit_hot_return=1" -d "opcache.jit_hot_side_exit=1" -d "opcache.jit_max_root_traces=100000" -d "opcache.jit_max_side_traces=100000" -d "opcache.jit_max_exit_counters=100000" -d "opcache.protect_memory=1" -d "zend.assertions=1" -d "zend.exception_ignore_args=0" -d "zend.exception_string_param_max_len=15" -d "short_open_tag=0" -d "extension_dir=/home/phpfuzz/WorkSpace/flowfusion/php-src/modules/" -d "zend_extension=/home/phpfuzz/WorkSpace/flowfusion/php-src/modules/opcache.so" -d "session.auto_start=0" -d "zend.enable_gc=1" -d "opcache.enabled=1" -f "/home/phpfuzz/WorkSpace/flowfusion/php-src/tests/fused/fused559.php"  2>&1
    ;;
esac