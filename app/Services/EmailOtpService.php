<?php

namespace App\Services;

/**
 * Service for handling email and extracting OTP.
 */
class EmailOtpService
{
    /**
     * Fetch OTP from email.
     *
     * @param string $email The email address to check for the OTP.
     * @param string $password The password for the email account.
     * @param int $timeout The maximum time (in seconds) to wait for the OTP email (default is 300 seconds).
     * @param int $interval The interval (in seconds) to wait between checking for new emails (default is 10 seconds).
     * @return string|null The extracted OTP if found, otherwise null.
     */
    public function fetchOtpFromEmail(string $email, string $password, int $timeout = 300, int $interval = 10): ?string
    {
        sleep(30);
        $searchSubject = "Email Verification Code -";
        $hostname = $this->getImapHostname($email);
        $inbox = imap_open($hostname, $email, $password) or die('Cannot connect to IMAP: ' . imap_last_error());

        $startTime = time();
        $otp = null;

        while ((time() - $startTime) < $timeout) {
            $emails = imap_search($inbox, 'UNSEEN SUBJECT "' . $searchSubject . '"');

            if ($emails) {
                rsort($emails);
                $latestEmailId = $emails[0];
                $body = $this->getEmailBody($inbox, $latestEmailId);

                if (preg_match('/Your verification code is:\s*(\w+)/', $body, $matches)) {
                    $otp = $matches[1];
                    break;
                }
            }

            sleep($interval);
        }

        imap_close($inbox);
        return $otp;
    }

    /**
     * Retrieve the body content of an email.
     *
     * @param resource $inbox The IMAP resource for the email inbox.
     * @param int $emailNumber The ID of the email to fetch the body from.
     * @return string The content of the email body.
     */
    private function getEmailBody($inbox, int $emailNumber): string
    {
        $structure = imap_fetchstructure($inbox, $emailNumber);
        $body = '';

        if (isset($structure->parts)) {
            foreach ($structure->parts as $partNumber => $part) {
                if ($part->subtype === 'PLAIN') {
                    $body .= imap_fetchbody($inbox, $emailNumber, $partNumber + 1);
                } elseif ($part->subtype === 'HTML') {
                    $body .= imap_fetchbody($inbox, $emailNumber, $partNumber + 1);
                }
            }
        } else {
            $body = imap_fetchbody($inbox, $emailNumber, 1);
        }

        if ($structure->encoding == 3) {
            $body = base64_decode($body);
        } elseif ($structure->encoding == 4) {
            $body = quoted_printable_decode($body);
        }

        return $body;
    }

    /**
     * Determine the IMAP hostname based on the email domain.
     *
     * @param string $email The email address to determine the IMAP hostname for.
     * @return string The IMAP hostname corresponding to the email domain.
     */
    private function getImapHostname(string $email): string
    {
        $domain = substr(strrchr($email, "@"), 1);
        return '{imap.' . $domain . ':993/imap/ssl}INBOX';
    }
}
