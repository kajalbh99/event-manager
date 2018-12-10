<?php
/* connect to gmail */
/* $hostname = '{mail.brucode.com:993/imap/ssl/novalidate-cert}INBOX';
 $username = 'Lovnish.kumar@brucode.com';
 $password = 'Hyr563#';*/

 $hostname = '{ippbx.com:995/pop/ssl/novalidate-cert}INBOX';
$username = 'test@ippbx.com';
$password = 'test#1234'; 

/* try to connect */
$inbox = imap_open($hostname,$username ,$password) or die('Cannot connect to Gmail: ' . imap_last_error());

/* grab emails */
$emails = imap_search($inbox,'ALL');

/* if emails are returned, cycle through each... */
if($emails) {

    /* begin output var */
	$output = '';
	/* put the newest emails on top */
    rsort($emails);

    /* for every email... */
	$count = 0;
     foreach($emails as $email_number) {

        //$overview = imap_fetch_overview($inbox,$email_number,0);
		//$message = imap_body($inbox,$email_number);
		$message = imap_fetchbody($inbox,$email_number,1, FT_PEEK);
		// echo imap_qprint(imap_body($inbox, $email_number)); 
		echo trim(quoted_printable_decode ($message))."<br>";
		 
			/* if($overview[0]->seen==0):

				$output.= 'From:  '.$overview[0]->from.'</br>';
				$output.= 'Subject:  '.$overview[0]->subject.'</br>';
				$output.= 'To:  '.$overview[0]->to.'</br>';
				$output.= 'Date:  '.$overview[0]->date.'</br>';
				$output.= 'Message Id:  '.$overview[0]->message_id.'</br>';
				$output.= 'References:  '.$overview[0]->references.'</br>';
				$output.= 'Reply To:  '.$overview[0]->in_reply_to.'</br>';
				$output.= 'Size:  '.$overview[0]->size.'</br>';
				$output.= 'Uid:  '.$overview[0]->uid.'</br>';
				$output.= 'Msgno:  '.$overview[0]->msgno.'</br>';
				$output.= 'Recent:  '.$overview[0]->recent.'</br>';
				$output.= 'Flagged:  '.$overview[0]->flagged.'</br>';
				$output.= 'Answered:  '.$overview[0]->answered.'</br>';
				$output.= 'Deleted:  '.$overview[0]->deleted.'</br>';
				$output.= 'Seen:  '.$overview[0]->seen.'</br>';
				$output.= 'Draft:  '.$overview[0]->draft.'</br>';
				$output.= 'Udate:  '.$overview[0]->udate.'</br></br></br></br>';
				$count++;
				echo $output;
			endif; */
		


    } 
	
	/* for ($i=1; $i <= 100; $i++)
	{
		$header = imap_header ($inbox,$i);
		
		$msg = imap_fetchbody ($inbox, 1, 'PEEK');
		$msgBody = imap fetchbody ($inbox, $i, '2.1');
		if ($msq Body =='')
		$portNo = '2. 1' ;
		$msgBody = imap_fetchbody ($inbox, $i, $portNo);
		$msgBody trim(substr (quoted_printable_decode ($msgBody), 0, 200);
		echo $msg.'<br>';
	} */
} 

/* close the connection */
imap_close($inbox);
?>