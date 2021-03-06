<?php
/**
 * This file is a part of the phpMussel package, and can be downloaded for free
 * from {@link https://github.com/Maikuolan/phpMussel/ GitHub}.
 *
 * PHPMUSSEL COPYRIGHT 2013 AND BEYOND BY THE PHPMUSSEL TEAM.
 *
 * Authors:
 * @see PEOPLE.md
 *
 * License: GNU/GPLv2
 * @see LICENSE.txt
 *
 * This file: CLI handler (last modified: 2017.03.30).
 */

/** Prevents execution from outside of phpMussel. */
if (!defined('phpMussel')) {
    die('[phpMussel] This should not be accessed directly.');
}

/** Prevents execution from outside of CLI-mode. */
if (!$phpMussel['Mussel_sapi'] || !$phpMussel['Mussel_PHP'] || $phpMussel['Mussel_OS'] != 'WIN') {
    die('[phpMussel] This should not be accessed directly.');
}

/** If CLI-mode is disabled, nothing here should be executed. */
if (!$phpMussel['Config']['general']['disable_cli']) {

    /** Check if any arguments have been parsed via CLI. */
    $phpMussel['cli_args'] = array(
        isset($argv[0]) ? $argv[0] : '',
        isset($argv[1]) ? $argv[1] : '',
        isset($argv[2]) ? $argv[2] : '',
        isset($argv[3]) ? $argv[3] : ''
    );

    /** Triggered by the forked child process in CLI-mode via Windows. */
    if ($phpMussel['cli_args'][1] == 'cli_win_scan') {
        /** Fetch the command. **/
        $phpMussel['cmd'] = strtolower((substr_count($phpMussel['cli_args'][2], ' ')) ? $phpMussel['substrbf']($phpMussel['cli_args'][2], ' ') : $phpMussel['cli_args'][2]);

        /** Scan a file or directory. **/
        if ($phpMussel['cmd'] == 'scan') {
            if ($phpMussel['Config']['general']['scan_cache_expiry']) {
                $phpMussel['PrepareHashCache']();
            }
            try {
                echo $phpMussel['Recursor'](substr($phpMussel['cli_args'][2], 5), true, true, 0, $phpMussel['cli_args'][3]);
            } catch (\Exception $e) {
                die($e->getMessage());
            }
            if ($phpMussel['Config']['general']['scan_cache_expiry']) {
                reset($phpMussel['HashCache']['Data']);
                $phpMussel['HashCache']['Count'] = count($phpMussel['HashCache']['Data']);
                for (
                    $phpMussel['HashCache']['Index'] = 0;
                    $phpMussel['HashCache']['Index'] < $phpMussel['HashCache']['Count'];
                    $phpMussel['HashCache']['Index']++
                ) {
                    $phpMussel['HashCache']['Key'] = key($phpMussel['HashCache']['Data']);
                    if (is_array($phpMussel['HashCache']['Data'][$phpMussel['HashCache']['Key']])) {
                        $phpMussel['HashCache']['Data'][$phpMussel['HashCache']['Key']] =
                            implode(':', $phpMussel['HashCache']['Data'][$phpMussel['HashCache']['Key']]) . ';';
                    }
                    next($phpMussel['HashCache']['Data']);
                }
                $phpMussel['HashCache']['Data'] = implode('', $phpMussel['HashCache']['Data']);
                $phpMussel['HashCache']['Data'] = $phpMussel['SaveCache'](
                    'HashCache',
                    $phpMussel['Time'] + $phpMussel['Config']['general']['scan_cache_expiry'],
                    $phpMussel['HashCache']['Data']
                );
            }
            die;
        }

        /** Generate an MD5 signature using a file. **/
        if ($phpMussel['cmd'] == 'md5_file') {
            $stl = substr($phpMussel['cli_args'][2], strlen($phpMussel['cmd']) + 1);
            if (is_dir($stl)) {
                if (!$d = @scandir($stl)) {
                    echo $phpMussel['lang']['failed_to_access'] . '"' . $stl . "\".\n";
                } else {
                    $c = count($d);
                    $xsc = $stl[strlen($stl) - 1];
                    if ($xsc !== "\\" && $xsc !== "/") {
                        $stl .= "/";
                    }
                    for ($i = 0; $i < $c; $i++) {
                        if ($d[$i] == '.'|| $d[$i] == '..') {
                            continue;
                        }
                        echo $phpMussel['Fork']('md5_file ' . $stl . $d[$i], $d[$i]) . "\n";
                    }
                }
            } elseif (is_file($stl)) {
                $hashme = $phpMussel['ReadFile']($stl, 0, true);
                echo md5($hashme) . ':' . strlen($hashme) . ":YOUR-SIGNATURE-NAME\n";
            } else {
                echo $stl . $phpMussel['lang']['cli_is_not_a'] . "\n";
            }
        }

        /** Generate a CoEx signature using a file. **/
        if ($phpMussel['cmd'] == 'coex_file') {
            $stl = substr($phpMussel['cli_args'][2], strlen($phpMussel['cmd']) + 1);
            if (is_dir($stl)) {
                if (!$d = @scandir($stl)) {
                    echo $phpMussel['lang']['failed_to_access'] . '"' . $stl . "\".\n";
                } else {
                    $c = count($d);
                    $xsc = $stl[strlen($stl) - 1];
                    if ($xsc !== "\\" && $xsc !== "/") {
                        $stl .= "/";
                    }
                    for ($i = 0; $i < $c; $i++) {
                        if ($d[$i] == '.'||$d[$i] == '..') {
                            continue;
                        }
                        echo $phpMussel['Fork']('coex_file ' . $stl . $d[$i], $d[$i]) . "\n";
                    }
                }
            } elseif (is_file($stl)) {
                $hashme = $phpMussel['ReadFile']($stl, 0, true);
                echo '$md5:' . md5($hashme) . ';$sha:' . sha1($hashme) . ';$str_len:' . strlen($hashme) . ";YOUR-SIGNATURE-NAME\n";
            }
            else echo $stl . $phpMussel['lang']['cli_is_not_a'] . "\n";
        }

        /** Fetch PE metadata. **/
        if ($phpMussel['cmd'] == 'pe_meta') {
            $stl = substr($phpMussel['cli_args'][2], strlen($phpMussel['cmd']) + 1);
            if (is_dir($stl)) {
                if (!$d = @scandir($stl)) {
                    echo $phpMussel['lang']['failed_to_access'] . '"' . $stl . "\".\n";
                } else {
                    $c = count($d);
                    $xsc = $stl[strlen($stl) - 1];
                    if ($xsc !== "\\" && $xsc !== "/") {
                        $stl .= "/";
                    }
                    for ($i = 0; $i < $c; $i++) {
                        if ($d[$i] == '.' || $d[$i] == '..') {
                            continue;
                        }
                        echo $phpMussel['Fork']('pe_meta ' . $stl . $d[$i], $d[$i]) . "\n";
                    }
                }
            } elseif (is_file($stl)) {
                $hashme = $phpMussel['ReadFile']($stl, 0, true);
                if (substr($hashme, 0, 2) === 'MZ') {
                    $PEArr = array();
                    $PEArr['Len'] = strlen($hashme);
                    $PEArr['Offset'] = $phpMussel['UnpackSafe']('S', substr($hashme, 60, 4));
                    $PEArr['Offset'] = $PEArr['Offset'][1];
                    while (true) {
                        $PEArr['DoScan'] = true;
                        if ($PEArr['Offset'] < 1 || $PEArr['Offset'] > 16384 || $PEArr['Offset'] > $PEArr['Len']) {
                            $PEArr['DoScan'] = false;
                            break;
                        }
                        $PEArr['Magic'] = substr($hashme, $PEArr['Offset'], 2);
                        if ($PEArr['Magic'] !== 'PE') {
                            $PEArr['DoScan'] = false;
                            break;
                        }
                        $PEArr['Proc'] = $phpMussel['UnpackSafe']('S', substr($hashme, $PEArr['Offset'] + 4, 2));
                        $PEArr['Proc'] = $PEArr['Proc'][1];
                        if ($PEArr['Proc'] != 0x14c && $PEArr['Proc'] != 0x8664) {
                            $PEArr['DoScan'] = false;
                            break;
                        }
                        $PEArr['NumOfSections'] = $phpMussel['UnpackSafe']('S', substr($hashme, $PEArr['Offset'] + 6, 2));
                        $PEArr['NumOfSections'] = $PEArr['NumOfSections'][1];
                        if ($PEArr['NumOfSections'] < 1 || $PEArr['NumOfSections'] > 40) {
                            $PEArr['DoScan'] = false;
                        }
                        break;
                    }
                    if (!$PEArr['DoScan']) {
                        echo $phpMussel['lang']['cli_pe1'] . "\n";
                    } else {
                        $PEArr['OptHdrSize'] = $phpMussel['UnpackSafe']('S', substr($hashme, $PEArr['Offset'] + 20, 2));
                        $PEArr['OptHdrSize'] = $PEArr['OptHdrSize'][1];
                        echo $phpMussel['lang']['cli_pe2'] . "\n";
                        for ($PEArr['k'] = 0; $PEArr['k'] < $PEArr['NumOfSections']; $PEArr['k']++) {
                            $PEArr['SectionHead'] = substr($hashme, $PEArr['Offset'] + 24 + $PEArr['OptHdrSize'] + ($PEArr['k'] * 40), $PEArr['NumOfSections'] * 40);
                            $PEArr['SectionName'] = str_ireplace("\x00", '', substr($PEArr['SectionHead'], 0, 8));
                            $PEArr['VirtualSize'] = $phpMussel['UnpackSafe']('S', substr($PEArr['SectionHead'], 8, 4));
                            $PEArr['VirtualSize'] = $PEArr['VirtualSize'][1];
                            $PEArr['VirtualAddress'] = $phpMussel['UnpackSafe']('S', substr($PEArr['SectionHead'], 12, 4));
                            $PEArr['VirtualAddress'] = $PEArr['VirtualAddress'][1];
                            $PEArr['SizeOfRawData'] = $phpMussel['UnpackSafe']('S', substr($PEArr['SectionHead'], 16, 4));
                            $PEArr['SizeOfRawData'] = $PEArr['SizeOfRawData'][1];
                            $PEArr['PointerToRawData'] = $phpMussel['UnpackSafe']('S', substr($PEArr['SectionHead'], 20, 4));
                            $PEArr['PointerToRawData'] = $PEArr['PointerToRawData'][1];
                            $PEArr['SectionData'] = substr($hashme, $PEArr['PointerToRawData'], $PEArr['SizeOfRawData']);
                            $PEArr['MD5'] = md5($PEArr['SectionData']);
                            echo $PEArr['SizeOfRawData'] . ':' . $PEArr['MD5'] . ':' . $PEArr['SectionName'] . "\n";
                        }
                        echo "\n";
                        if (substr_count($hashme, "V\x00a\x00r\x00F\x00i\x00l\x00e\x00I\x00n\x00f\x00o\x00\x00\x00\x00\x00\x24")) {
                            $PEArr['FINFO'] = $phpMussel['substral']($hashme, "V\x00a\x00r\x00F\x00i\x00l\x00e\x00I\x00n\x00f\x00o\x00\x00\x00\x00\x00\x24");
                            if (substr_count($PEArr['FINFO'], "F\x00i\x00l\x00e\x00D\x00e\x00s\x00c\x00r\x00i\x00p\x00t\x00i\x00o\x00n\x00\x00\x00")) {
                                $PEArr['ThisData'] = trim(str_ireplace("\x00", '', $phpMussel['substrbf']($phpMussel['substral']($PEArr['FINFO'], "F\x00i\x00l\x00e\x00D\x00e\x00s\x00c\x00r\x00i\x00p\x00t\x00i\x00o\x00n\x00\x00\x00"), "\x00\x00\x00")));
                                echo '$PEFileDescription:' . md5($PEArr['ThisData']) . ':' . strlen($PEArr['ThisData']) . ":YOUR-SIGNATURE-NAME\n";
                            }
                            if (substr_count($PEArr['FINFO'], "F\x00i\x00l\x00e\x00V\x00e\x00r\x00s\x00i\x00o\x00n\x00\x00\x00")) {
                                $PEArr['ThisData'] = trim(str_ireplace("\x00", '', $phpMussel['substrbf']($phpMussel['substral']($PEArr['FINFO'], "F\x00i\x00l\x00e\x00V\x00e\x00r\x00s\x00i\x00o\x00n\x00\x00\x00"), "\x00\x00\x00")));
                                echo '$PEFileVersion:' . md5($PEArr['ThisData']) . ':' . strlen($PEArr['ThisData']) . ":YOUR-SIGNATURE-NAME\n";
                            }
                            if (substr_count($PEArr['FINFO'], "P\x00r\x00o\x00d\x00u\x00c\x00t\x00N\x00a\x00m\x00e\x00\x00\x00")) {
                                $PEArr['ThisData'] = trim(str_ireplace("\x00", '', $phpMussel['substrbf']($phpMussel['substral']($PEArr['FINFO'], "P\x00r\x00o\x00d\x00u\x00c\x00t\x00N\x00a\x00m\x00e\x00\x00\x00"), "\x00\x00\x00")));
                                echo '$PEProductName:' . md5($PEArr['ThisData']) . ':' . strlen($PEArr['ThisData']) . ":YOUR-SIGNATURE-NAME\n";
                            }
                            if (substr_count($PEArr['FINFO'], "P\x00r\x00o\x00d\x00u\x00c\x00t\x00V\x00e\x00r\x00s\x00i\x00o\x00n\x00\x00\x00")) {
                                $PEArr['ThisData'] = trim(str_ireplace("\x00", '', $phpMussel['substrbf']($phpMussel['substral']($PEArr['FINFO'], "P\x00r\x00o\x00d\x00u\x00c\x00t\x00V\x00e\x00r\x00s\x00i\x00o\x00n\x00\x00\x00"), "\x00\x00\x00")));
                                echo '$PEProductVersion:' . md5($PEArr['ThisData']) . ':' . strlen($PEArr['ThisData']) . ":YOUR-SIGNATURE-NAME\n";
                                }
                            if (substr_count($PEArr['FINFO'], "L\x00e\x00g\x00a\x00l\x00C\x00o\x00p\x00y\x00r\x00i\x00g\x00h\x00t\x00\x00\x00")) {
                                $PEArr['ThisData'] = trim(str_ireplace("\x00", '', $phpMussel['substrbf']($phpMussel['substral']($PEArr['FINFO'], "L\x00e\x00g\x00a\x00l\x00C\x00o\x00p\x00y\x00r\x00i\x00g\x00h\x00t\x00\x00\x00"), "\x00\x00\x00")));
                                echo '$PECopyright:' . md5($PEArr['ThisData']) . ':' . strlen($PEArr['ThisData']) . ":YOUR-SIGNATURE-NAME\n";
                            }
                            if (substr_count($PEArr['FINFO'], "O\x00r\x00i\x00g\x00i\x00n\x00a\x00l\x00F\x00i\x00l\x00e\x00n\x00a\x00m\x00e\x00\x00\x00")) {
                                $PEArr['ThisData'] = trim(str_ireplace("\x00", '', $phpMussel['substrbf']($phpMussel['substral']($PEArr['FINFO'], "O\x00r\x00i\x00g\x00i\x00n\x00a\x00l\x00F\x00i\x00l\x00e\x00n\x00a\x00m\x00e\x00\x00\x00"), "\x00\x00\x00")));
                                echo '$PEOriginalFilename:' . md5($PEArr['ThisData']) . ':' . strlen($PEArr['ThisData']) . ":YOUR-SIGNATURE-NAME\n";
                            }
                            if (substr_count($PEArr['FINFO'], "C\x00o\x00m\x00p\x00a\x00n\x00y\x00N\x00a\x00m\x00e\x00\x00\x00")) {
                                $PEArr['ThisData'] = trim(str_ireplace("\x00", '', $phpMussel['substrbf']($phpMussel['substral']($PEArr['FINFO'], "C\x00o\x00m\x00p\x00a\x00n\x00y\x00N\x00a\x00m\x00e\x00\x00\x00"), "\x00\x00\x00")));
                                echo '$PECompanyName:' . md5($PEArr['ThisData']) . ':' . strlen($PEArr['ThisData']) . ":YOUR-SIGNATURE-NAME\n";
                            }
                        }
                    }
                    $PEArr = false;
                } else {
                    echo $phpMussel['lang']['cli_pe1'] . "\n";
                }
            } else {
                echo $stl . $phpMussel['lang']['cli_is_not_a'] . "\n";
            }
        }

        /** Die child process back to parent. */
        die;
    }

    /** Echo the ASCII header art and CLI-mode information. */
    echo $phpMussel['lang']['cli_ln1'] . $phpMussel['lang']['cli_ln2'] . $phpMussel['lang']['cli_ln3'];

    /** Open STDIN. */
    $sth = fopen('php://stdin', 'r');

    while (true) {

        /** Set CLI process title (PHP =>5.5.0). */
        if (function_exists('cli_set_process_title')) {
            cli_set_process_title($phpMussel['ScriptIdent']);
        }

        /** Echo the CLI-mode prompt. */
        echo $phpMussel['lang']['cli_prompt'];

        /** Wait for user input. */
        $stl = trim(fgets($sth));

        /** Set CLI process title with "working" notice (PHP =>5.5.0). */
        if (function_exists('cli_set_process_title')) {
            cli_set_process_title($phpMussel['ScriptIdent'] . ' - ' . $phpMussel['lang']['cli_working'] . '...');
        }

        /** Fetch the command. **/
        $phpMussel['cmd'] = strtolower((substr_count($stl, ' ')) ? $phpMussel['substrbf']($stl, ' ') : $stl);

        /** Exit CLI-mode. **/
        if ($phpMussel['cmd'] == 'quit' || $phpMussel['cmd'] == 'q' || $phpMussel['cmd'] == 'exit') {
            die;
        }

        /** Generate an MD5 signature using a file. **/
        if ($phpMussel['cmd'] == 'md5_file' || $phpMussel['cmd'] == 'm') {
            echo "\n";
            $stl = substr($stl, strlen($phpMussel['cmd']) + 1);
            if (is_dir($stl)) {
                if (!$d = @scandir($stl)) {
                    echo $phpMussel['lang']['failed_to_access'] . '"' . $stl . "\".\n";
                } else {
                    $xsc = $stl[strlen($stl) - 1];
                    if ($xsc !== "\\" && $xsc !== "/") {
                        $stl .= "/";
                    }
                    foreach ($d as $i) {
                        if ($i === '.' || $i === '..') {
                            continue;
                        }
                        echo $phpMussel['Fork']('md5_file ' . $stl . $i, $i) . "\n";
                    }
                }
            } elseif (is_file($stl)) {
                $hashme = $phpMussel['ReadFile']($stl, 0, true);
                echo md5($hashme) . ':' . strlen($hashme) . ":YOUR-SIGNATURE-NAME\n";
            } else {
                echo $stl . $phpMussel['lang']['cli_is_not_a'] . "\n";
            }
        }

        /** Generate a CoEx signature using a file. **/
        if ($phpMussel['cmd'] == 'coex_file') {
            echo "\n";
            $stl = substr($stl, strlen($phpMussel['cmd']) + 1);
            if (is_dir($stl)) {
                if (!$d = @scandir($stl)) {
                    echo $phpMussel['lang']['failed_to_access'] . '"' . $stl . "\".\n";
                } else {
                    $xsc = $stl[strlen($stl) - 1];
                    if ($xsc !== "\\" && $xsc !== "/") {
                        $stl .= "/";
                    }
                    foreach ($d as $i) {
                        if ($i === '.' || $i === '..') {
                            continue;
                        }
                        echo $phpMussel['Fork']('coex_file ' . $stl . $i, $i) . "\n";
                    }
                }
            } elseif (is_file($stl)) {
                $hashme = $phpMussel['ReadFile']($stl, 0, true);
                echo '$md5:' . md5($hashme) . ';$sha:' . sha1($hashme) . ';$str_len:' . strlen($hashme) . ";YOUR-SIGNATURE-NAME\n";
            } else {
                echo $stl . $phpMussel['lang']['cli_is_not_a'] . "\n";
            }
        }

        /** Fetch PE metadata. **/
        if ($phpMussel['cmd'] == 'pe_meta') {
            echo "\n";
            $stl = substr($stl, strlen($phpMussel['cmd']) + 1);
            if (is_dir($stl)) {
                if (!$d = @scandir($stl)) {
                    echo $phpMussel['lang']['failed_to_access'] . '"' . $stl . "\".\n";
                } else {
                    $xsc = $stl[strlen($stl) - 1];
                    if ($xsc !== "\\" && $xsc !== "/") {
                        $stl .= "/";
                    }
                    foreach ($d as $i) {
                        if ($i === '.' || $i === '..') {
                            continue;
                        }
                        echo $phpMussel['Fork']('pe_meta ' . $stl . $i, $i) . "\n";
                    }
                }
            } elseif (is_file($stl)) {
                echo $phpMussel['Fork']('pe_meta ' . $stl, $stl) . "\n";
            } else {
                echo $stl . $phpMussel['lang']['cli_is_not_a'] . "\n";
            }
        }

        /** Generate an MD5 signature using a string. **/
        if ($phpMussel['cmd'] == 'md5') {
            echo "\n";
            $stl = substr($stl, strlen($phpMussel['cmd']) + 1);
            echo md5($stl) . ':' . strlen($stl) . ":YOUR-SIGNATURE-NAME\n";
        }

        /** Generate a URL scanner signature from a URL. **/
        if ($phpMussel['cmd'] == 'url_sig') {
            echo "\n";
            $stl = $phpMussel['prescan_normalise'](substr($stl, strlen($phpMussel['cmd']) + 1));
            $urlsig = array();
            $urlsig['avoidme'] = $urlsig['forthis'] = '';
            if (
                !preg_match_all('/(data|file|https?|ftps?|sftp|ss[hl])\:\/\/(www[0-9]{0,3}\.)?([0-9a-z.-]{1,512})/i', $stl, $urlsig['domain']) ||
                !preg_match_all('/(data|file|https?|ftps?|sftp|ss[hl])\:\/\/(www[0-9]{0,3}\.)?([\!\#\$\&-;\=\?\@-\[\]_a-z~]{1,4000})/i', $stl, $urlsig['url'])
            ) {
                echo $phpMussel['lang']['invalid_url'] . "\n";
            } else {
                echo 'DOMAIN:' . md5($urlsig['domain'][3][0]) . ':' . strlen($urlsig['domain'][3][0]) . ":YOUR-SIGNATURE-NAME\n";
                $urlsig['forthis'] = md5($urlsig['url'][3][0]) . ':' . strlen($urlsig['url'][3][0]);
                $urlsig['avoidme'] .= ',' . $urlsig['forthis'] . ',';
                echo 'URL:' . $urlsig['forthis'] . ":YOUR-SIGNATURE-NAME\n";
                if (preg_match('/[^0-9a-z.-]$/i', $urlsig['url'][3][0])) {
                    $urlsig['x'] = preg_replace('/[^0-9a-z.-]+$/i', '', $urlsig['url'][3][0]);
                    $urlsig['forthis'] = md5($urlsig['x']) . ':' . strlen($urlsig['x']);
                    if (!substr_count($urlsig['avoidme'], $urlsig['forthis'])) {
                        $urlsig['avoidme'] .= ',' . $urlsig['forthis'] . ',';
                        echo 'URL:' . $urlsig['forthis'] . ":YOUR-SIGNATURE-NAME\n";
                    }
                }
                if (substr_count($urlsig['url'][3][0], '?')) {
                    $urlsig['x'] = $phpMussel['substrbf']($urlsig['url'][3][0], '?');
                    $urlsig['forthis'] = md5($urlsig['x']) . ':' . strlen($urlsig['x']);
                    if (!substr_count($urlsig['avoidme'], $urlsig['forthis'])) {
                        $urlsig['avoidme'] .= ',' . $urlsig['forthis'] . ',';
                        echo 'URL:' . $urlsig['forthis'] . ":YOUR-SIGNATURE-NAME\n";
                    }
                    $urlsig['x'] = $phpMussel['substraf']($urlsig['url'][3][0], '?');
                    $urlsig['forthis'] = md5($urlsig['x']) . ':' . strlen($urlsig['x']);
                    if (
                        !substr_count($urlsig['avoidme'], $urlsig['forthis']) &&
                        $urlsig['forthis'] != 'd41d8cd98f00b204e9800998ecf8427e:0'
                    ) {
                        $urlsig['avoidme'] .= ',' . $urlsig['forthis'] . ',';
                        echo 'QUERY:' . $urlsig['forthis'] . ":YOUR-SIGNATURE-NAME\n";
                    }
                }
            }
            $urlsig = '';
        }

        /** Generate a CoEx signature using a string. **/
        if ($phpMussel['cmd'] == 'coex') {
            echo "\n";
            $stl = substr($stl, strlen($phpMussel['cmd']) + 1);
            echo '$md5:' . md5($stl) . ';$sha:' . sha1($stl) . ';$str_len:' . strlen($stl) . ";YOUR-SIGNATURE-NAME\n";
        }

        /** Convert a binary string to a hexadecimal. **/
        if ($phpMussel['cmd'] == 'hex_encode' || $phpMussel['cmd'] == 'x') {
            echo "\n";
            $stl = substr($stl, strlen($phpMussel['cmd']) + 1);
            echo bin2hex($stl) . "\n";
        }

        /** Convert a hexadecimal to a binary string. **/
        if ($phpMussel['cmd'] == 'hex_decode') {
            echo "\n";
            $stl = substr($stl, strlen($phpMussel['cmd']) + 1);
            echo $phpMussel['HexSafe']($stl) . "\n";
        }

        /** Convert a binary string to a base64 string. **/
        if ($phpMussel['cmd'] == 'base64_encode' || $phpMussel['cmd'] == 'b') {
            echo "\n";
            $stl = substr($stl, strlen($phpMussel['cmd']) + 1);
            echo base64_encode($stl) . "\n";
        }

        /** Convert a base64 string to a binary string. **/
        if ($phpMussel['cmd'] == 'base64_decode') {
            echo "\n";
            $stl = substr($stl, strlen($phpMussel['cmd']) + 1);
            echo base64_decode($stl) . "\n";
        }

        /** Scan a file or directory. **/
        if ($phpMussel['cmd'] == 'scan' || $phpMussel['cmd'] == 's') {
            echo "\n";
            $stl = substr($stl, strlen($phpMussel['cmd']) + 1);
            $out = $r = '';
            $phpMussel['memCache']['start_time'] = time() + ($phpMussel['Config']['general']['timeOffset'] * 60);
            $phpMussel['memCache']['start_time_2822'] = $phpMussel['TimeFormat']($phpMussel['memCache']['start_time'], $phpMussel['Config']['general']['timeFormat']);
            echo $s = $phpMussel['memCache']['start_time_2822'] . ' ' . $phpMussel['lang']['started'] . $phpMussel['lang']['_fullstop_final'] . "\n";
            if (is_dir($stl)) {
                if (!$d = @scandir($stl)) {
                    $out = '> ' . $phpMussel['lang']['failed_to_access'] . '"' . $stl . "\".\n";
                } else {
                    $c = count($d);
                    $xsc = $stl[strlen($stl) - 1];
                    if ($xsc !== "\\" && $xsc !== "/") {
                        $stl .= "/";
                    }
                    for ($i = 0; $i < $c; $i++) {
                        if ($d[$i] == '.' || $d[$i] == '..') {
                            continue;
                        }
                        $pcent = round(($i / $c) * 100, 2) . '%';
                        echo $pcent . ' ' . $phpMussel['lang']['scan_complete'] . $phpMussel['lang']['_fullstop_final'];
                        $out = $phpMussel['Fork']('scan ' . $stl . $d[$i], $d[$i]);
                        if (!$out) {
                            $out = '> ' . $phpMussel['lang']['cli_failed_to_complete'] . ' (' . $d[$i] . ')' . $phpMussel['lang']['_exclamation_final'] . "\n";
                        }
                        $r .= $out;
                        echo "\r" . $phpMussel['prescan_decode']($out);
                        $out = '';
                    }
                }
            } elseif (is_file($stl)) {
                $out = $phpMussel['Fork']('scan ' . $stl, $stl);
                if (!$out) {
                    $out = '> ' . $phpMussel['lang']['cli_failed_to_complete'] . $phpMussel['lang']['_exclamation_final'] . "\n";
                }
            } elseif (!$out) {
                $out = '> ' . $stl . $phpMussel['lang']['cli_is_not_a'] . "\n";
            }
            $r .= $out;
            if ($out) {
                echo $phpMussel['prescan_decode']($out);
                $out = '';
            }
            $phpMussel['memCache']['end_time'] = time() + ($phpMussel['Config']['general']['timeOffset'] * 60);
            $phpMussel['memCache']['end_time_2822'] = $phpMussel['TimeFormat']($phpMussel['memCache']['end_time'], $phpMussel['Config']['general']['timeFormat']);
            /** @todo Get serialised logging working for CLI mode (github.com/Maikuolan/phpMussel/issues/54). */
            $r = $s . $r;
            $s = $phpMussel['memCache']['end_time_2822'] . ' ' . $phpMussel['lang']['finished'] . $phpMussel['lang']['_fullstop_final'] . "\n";
            echo $s;
            $r .= $s;
            if ($phpMussel['Config']['general']['scan_log']) {
                $phpMussel['memCache']['handle'] = array(
                    'File' => $phpMussel['TimeFormat']($phpMussel['Time'], $phpMussel['Config']['general']['scan_log'])
                );
                if (!file_exists($phpMussel['Vault'] . $phpMussel['memCache']['handle']['File'])) {
                    $r = $phpMussel['safety'] . "\n" . $r;
                }
                $phpMussel['memCache']['handle'] =
                    fopen($phpMussel['Vault'] . $phpMussel['memCache']['handle']['File'], 'a');
                fwrite($phpMussel['memCache']['handle'], $r);
                fclose($phpMussel['memCache']['handle']);
                $phpMussel['memCache']['handle'] = '';
            }
            $s = $r = '';
        }

        /** Add an entry to the greylist. **/
        if ($phpMussel['cmd'] == 'greylist' || $phpMussel['cmd'] == 'g') {
            echo "\n";
            $stl = substr($stl, strlen($phpMussel['cmd']) + 1);
            if (!empty($stl)) {
                $greylist = (!file_exists($phpMussel['Vault'] . 'greylist.csv')) ? ',' : '';
                $greylist .= $stl . ',';
                $handle = fopen($phpMussel['Vault'] . 'greylist.csv', 'a');
                fwrite($handle, $greylist);
                fclose($handle);
                unset($handle, $greylist);
                echo $phpMussel['lang']['greylist_updated'];
            }
        }

        /** Clear the greylist. **/
        if ($phpMussel['cmd'] == 'greylist_clear' || $phpMussel['cmd'] == 'gc') {
            echo "\n";
            $handle = fopen($phpMussel['Vault'] . 'greylist.csv', 'a');
            ftruncate($handle, 0);
            fwrite($handle, ',');
            fclose($handle);
            unset($handle, $greylist);
            echo $phpMussel['lang']['greylist_cleared'];
        }

        /** Show the greylist. **/
        if ($phpMussel['cmd'] == 'greylist_show' || $phpMussel['cmd'] == 'gs') {
            echo "\n";
            $stl = substr($stl, strlen($phpMussel['cmd']) + 1);
            echo
                (file_exists($phpMussel['Vault'] . 'greylist.csv')) ?
                " greylist.csv:\n" . implode("\n ", explode(',', $phpMussel['ReadFile']($phpMussel['Vault'] . 'greylist.csv'))) :
                ' greylist.csv ' . $phpMussel['lang']['x_does_not_exist'] . $phpMussel['lang']['_exclamation_final'];
        }

        /** Print the command list. **/
        if ($phpMussel['cmd'] == 'c') {
            echo $phpMussel['lang']['cli_commands'];
        }

    }

    die;
}
