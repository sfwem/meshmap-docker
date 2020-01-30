<?php
function gethostbyaddr_timeout( $ip, $dns, $timeout = 3 ) {
	// idea from http://www.php.net/manual/en/function.gethostbyaddr.php#46869
	// http://www.askapache.com/pub/php/gethostbyaddr.php

	// random transaction number (for routers etc to get the reply back)
	$data = rand( 10, 77 ) . "\1\0\0\1\0\0\0\0\0\0";

	// octals in the array, keys are strlen of bit
	$bitso = array("","\1","\2","\3" );
	foreach( array_reverse( explode( '.', $ip ) ) as $bit ) {
		$l=strlen($bit);
		$data.="{$bitso[$l]}".$bit;
	}

	// and the final bit of the request
	$data .= "\7in-addr\4arpa\0\0\x0C\0\1";

	// create UDP socket
	$errno = $errstr = 0;
	$fp = fsockopen( "udp://{$dns}", 53, $errno, $errstr, $timeout );
	if( ! $fp || ! is_resource( $fp ) )
		return $errno;

		if( function_exists( 'socket_set_timeout' ) ) {
			socket_set_timeout( $fp, $timeout );
		} elseif ( function_exists( 'stream_set_timeout' ) ) {
			stream_set_timeout( $fp, $timeout );
		}

		// send our request (and store request size so we can cheat later)
		$requestsize = fwrite( $fp, $data );
		$max_rx = $requestsize * 3;
		$response = '';
		$start = time();
		$responsesize = 0;
		while ( $responsesize < $max_rx && ( ( time() - $start ) < $timeout ) && ($buf = fread( $fp, 1 ) ) !== false ) {
			$responsesize++;
			$response .= $buf;
		}
		// echo "[tx: $requestsize bytes]  [rx: {$responsesize} bytes]";

		// hope we get a reply
		if ( is_resource( $fp ) )
			fclose( $fp );

			// if empty response or bad response, return original ip
			if ( empty( $response ) || bin2hex( substr( $response, $requestsize + 2, 2 ) ) != '000c' )
				return $ip;

				// set up our variables
				$host = '';
				$len = $loops = 0;

				// set our pointer at the beginning of the hostname uses the request size from earlier rather than work it out
				$pos = $requestsize + 12;
				do {
					// get segment size
					$len = unpack( 'c', substr( $response, $pos, 1 ) );

					// null terminated string, so length 0 = finished - return the hostname, without the trailing .
					if ( $len[1] == 0 )
						return substr( $host, 0, -1 );

						// add segment to our host
						$host .= substr( $response, $pos + 1, $len[1] ) . '.';

						// move pointer on to the next segment
						$pos += $len[1] + 1;

						// recursion protection
						$loops++;
				}
				while ( $len[1] != 0 && $loops < 20 );

				// return the ip in case
				return $ip;
}
?>