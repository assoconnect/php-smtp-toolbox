<?php

namespace AssoConnect\SmtpToolbox\Resolver;

class BounceTypeResolver
{
    public const BOUNCE_REASON_UNQUALIFIED = 'BOUNCE_REASON_UNQUALIFIED';
    public const BOUNCE_REASON_UNKNOWN = 'BOUNCE_REASON_UNKNOWN';
    public const BOUNCE_REASON_DMARC_FAILURE = 'BOUNCE_REASON_DMARC_FAILURE';
    public const BOUNCE_REASON_BLACKLISTED = 'BOUNCE_REASON_BLACKLISTED';
    public const BOUNCE_REASON_GREYLISTING = 'BOUNCE_REASON_GREYLISTING';
    public const BOUNCE_REASON_SPAMMY = 'BOUNCE_REASON_SPAMMY';
    public const BOUNCE_REASON_DENIED = 'BOUNCE_REASON_DENIED';
    public const BOUNCE_REASON_USER_ACTION_REQUIRED = 'BOUNCE_REASON_USER_ACTION_REQUIRED';
    public const BOUNCE_REASON_INVALID = 'BOUNCE_REASON_INVALID';
    public const BOUNCE_REASON_TEMPORARY = 'BOUNCE_REASON_TEMPORARY';
    public const BOUNCE_REASONS_MAPPING = [
        //DMarc failures
        '/Email rejected per DMARC policy/' => self::BOUNCE_REASON_DMARC_FAILURE,
        '/does not pass DMARC verification/' => self::BOUNCE_REASON_DMARC_FAILURE,
        '/OFR004_519/' => self::BOUNCE_REASON_DMARC_FAILURE,
        '/DMARC policy/' => self::BOUNCE_REASON_DMARC_FAILURE,
        '/OFR_515/' => self::BOUNCE_REASON_DMARC_FAILURE,
        // Blacklisting bounces
        '/Your access to this mail system has been rejected due to poor reputation of a domain used in message transfer/' => self::BOUNCE_REASON_BLACKLISTED,
        '/Your email was rejected due to having a domain present in the Spamhaus DBL/' => self::BOUNCE_REASON_BLACKLISTED,
        '/MailFrom domain is listed in Spamhaus/' => self::BOUNCE_REASON_BLACKLISTED,
        '/LPN007_510/' => self::BOUNCE_REASON_BLACKLISTED,
        '/OFR_506/' => self::BOUNCE_REASON_BLACKLISTED,
        '/SFR_IN_103/' => self::BOUNCE_REASON_BLACKLISTED,
        '/Blocked - see https:\/\/www\.spamcop\.net/' => self::BOUNCE_REASON_BLACKLISTED,
        '/Comcast block for spam/' => self::BOUNCE_REASON_BLACKLISTED,
        '/Host blacklisted/' => self::BOUNCE_REASON_BLACKLISTED,
        '/Client host .* blocked by bl.spamcop.net/' => self::BOUNCE_REASON_BLACKLISTED,
        '/Your access to this mail system has been rejected due to the sending MTA\'s poor reputation/' => self::BOUNCE_REASON_BLACKLISTED,
        '/Server IP .* listed as abusive/' => self::BOUNCE_REASON_BLACKLISTED,
        '/The sender .* is in a black list bl.spamcop.net/' => self::BOUNCE_REASON_BLACKLISTED,
        '/OFR_999/' => self::BOUNCE_REASON_BLACKLISTED,
        '/Too much Spam from this IP/' => self::BOUNCE_REASON_BLACKLISTED,
        '/too many errors detected from your IP/' => self::BOUNCE_REASON_BLACKLISTED,
        '/Client host rejected: Access denied/' => self::BOUNCE_REASON_BLACKLISTED,
        // Spam content detected
        '/JunkMail rejected/' => self::BOUNCE_REASON_SPAMMY,
        '/Sophos Anti Spam Engine has blocked this Email/' => self::BOUNCE_REASON_SPAMMY,
        '/Spam blocked/' => self::BOUNCE_REASON_SPAMMY,
        '/spam detected/' => self::BOUNCE_REASON_SPAMMY,
        '/Message not accepted for policy reasons/' => self::BOUNCE_REASON_SPAMMY,
        // Email denied
        '/5\.7\.1 <.*>: Recipient address rejected: Access denied/' => self::BOUNCE_REASON_DENIED,
        '/5\.7\.1 <.*>: Relay access denied/' => self::BOUNCE_REASON_DENIED,
        '/5\.7\.1 Connection refused/' => self::BOUNCE_REASON_DENIED,
        '/The message was delivered, but was either blocked by the user, or classified as spam, bulk mail, or had rejected content/' => self::BOUNCE_REASON_DENIED,
        '/Sender denied as sender\'s email address is on SenderFilterConfig list/' => self::BOUNCE_REASON_DENIED,
        '/Access denied/' => self::BOUNCE_REASON_DENIED,
        // Greylisting
        '/Recipient address rejected: Greylisted, see http:\/\/postgrey\.schweikert\.ch/' => self::BOUNCE_REASON_DENIED,
        '/Greylisting in (effect|action), please come back later/' => self::BOUNCE_REASON_GREYLISTING,
        '/Greylisted for .*/' => self::BOUNCE_REASON_GREYLISTING,
        '/(You email|message) has been greylisted/' => self::BOUNCE_REASON_GREYLISTING,
        '/Temporary delay by Postgrey/' => self::BOUNCE_REASON_GREYLISTING,
        '/This server uses greylisting/' => self::BOUNCE_REASON_GREYLISTING,
        // Invalid recipient bounces
        '/5.5.0 recipient rejected/' => self::BOUNCE_REASON_INVALID,
        '/Invalid messages/' => self::BOUNCE_REASON_INVALID,
        '/mailbox is disabled/' => self::BOUNCE_REASON_INVALID,
        '/Not Our Customer/' => self::BOUNCE_REASON_INVALID,
        '/Domain does not exist/' => self::BOUNCE_REASON_INVALID,
        '/mailbox not found/' => self::BOUNCE_REASON_INVALID,
        '/mailbox unavailable/' => self::BOUNCE_REASON_INVALID,
        '/does not exist/' => self::BOUNCE_REASON_INVALID,
        '/(MX|Recipient address) lookup failed/' => self::BOUNCE_REASON_INVALID,
        '/email account that you tried to reach (does not exist|is over quota and inactive|is inactive)/' => self::BOUNCE_REASON_INVALID,
        '/User unknown in local recipient table/' => self::BOUNCE_REASON_INVALID,
        '/unable to connect to MX servers/' => self::BOUNCE_REASON_INVALID,
        '/new mail is not currently being accepted for this mailbox/' => self::BOUNCE_REASON_INVALID,
        '/Addressee unknown/' => self::BOUNCE_REASON_INVALID,
        '/verify that you have the correct email address for your recipient/' => self::BOUNCE_REASON_INVALID,
        '/mailbox is inactive and has been disabled/' => self::BOUNCE_REASON_INVALID,
        '/Email address could not be found/' => self::BOUNCE_REASON_INVALID,
        '/No such user/i' => self::BOUNCE_REASON_INVALID,
        '/User Unknown/i' => self::BOUNCE_REASON_INVALID,
        '/Account Closed, Please Remove/' => self::BOUNCE_REASON_INVALID,
        '/no valid recipients/' => self::BOUNCE_REASON_INVALID,
        '/RESOLVER\.ADR\.Recip(?:ient)?NotFound/' => self::BOUNCE_REASON_INVALID,
        '/Account Inactive/' => self::BOUNCE_REASON_INVALID,
        '/Invalid Recipient/' => self::BOUNCE_REASON_INVALID,
        '/Destinataire invalide/' => self::BOUNCE_REASON_INVALID,
        '/domain accepts no mail/' => self::BOUNCE_REASON_INVALID,
        '/RESOLVER\.RST\.RestrictedToRecipientsPermission/' => self::BOUNCE_REASON_INVALID,
        '/Envelope blocked - User Entry/' => self::BOUNCE_REASON_INVALID,
        '/5\.7\.1.* gsmtp/' => self::BOUNCE_REASON_INVALID,
        '/OFR_416/' => self::BOUNCE_REASON_INVALID,
        '/LPN007_416/' => self::BOUNCE_REASON_INVALID,
        '/No MX for/' => self::BOUNCE_REASON_INVALID,
        '/blocked due to inactivity/' => self::BOUNCE_REASON_INVALID,
        '/mailbox is unavailable/' => self::BOUNCE_REASON_INVALID,
        '/incoming to a recipient domain configured in a hosted tenant which has no mail-enabled subscriptions/' => self::BOUNCE_REASON_INVALID,
        '/mailbox temporarily disabled/' => self::BOUNCE_REASON_INVALID,
        '/The account or domain may not exist/' => self::BOUNCE_REASON_INVALID,
        '/email to be undeliverable/' => self::BOUNCE_REASON_INVALID,
        '/Unable to classify the NDR/' => self::BOUNCE_REASON_INVALID,
        '/Utilisateur inconnu/' => self::BOUNCE_REASON_INVALID,
        '/(4|5)\.1\.1/' => self::BOUNCE_REASON_INVALID,
        // User action required
        '/(user|MailBox|Mailbox) quota (exceeded|excedeed)/' => self::BOUNCE_REASON_USER_ACTION_REQUIRED,
        '/5\.2\.2 <.*>: (Over quota|user is over quota)/' => self::BOUNCE_REASON_USER_ACTION_REQUIRED,
        '/STOREDRV\.Deliver\.Exception:QuotaExceededException/' => self::BOUNCE_REASON_USER_ACTION_REQUIRED,
        '/5\.2\.2 <.*>: .* Quota exceeded (mailbox for user is full)/' => self::BOUNCE_REASON_USER_ACTION_REQUIRED,
        '/inbox is out of storage space/' => self::BOUNCE_REASON_USER_ACTION_REQUIRED,
        '/Hop count exceeded - possible mail loop/' => self::BOUNCE_REASON_USER_ACTION_REQUIRED,
        '/Quota exceeded/' => self::BOUNCE_REASON_USER_ACTION_REQUIRED,
        '/OFR_417/' => self::BOUNCE_REASON_USER_ACTION_REQUIRED,
        '/over quota/' => self::BOUNCE_REASON_USER_ACTION_REQUIRED,
        '/Insufficient system storage/' => self::BOUNCE_REASON_USER_ACTION_REQUIRED,
        '/mailbox is full/i' => self::BOUNCE_REASON_USER_ACTION_REQUIRED,
        '/Over quota/' => self::BOUNCE_REASON_USER_ACTION_REQUIRED,
        // Temporary failure
        '/try again later/i' => self::BOUNCE_REASON_TEMPORARY,
        '/The server could not temporarily deliver your message/' => self::BOUNCE_REASON_TEMPORARY,
        '/Unable to temporarily deliver message/' => self::BOUNCE_REASON_TEMPORARY,
        '/4\.7\.1 Try again later/' => self::BOUNCE_REASON_TEMPORARY,
        '/4\.3\.2 Please try again later/' => self::BOUNCE_REASON_TEMPORARY,
        '/4\.4\.2 .* Error: timeout exceeded/' => self::BOUNCE_REASON_TEMPORARY,
        '/A temporary DNS error/' => self::BOUNCE_REASON_TEMPORARY,
        '/5\.4\.1 Recipient address rejected: Access denied/' => self::BOUNCE_REASON_TEMPORARY,
        '/Too much load; please try again later/' => self::BOUNCE_REASON_TEMPORARY,
        '/Too many messages for this session/' => self::BOUNCE_REASON_TEMPORARY,
        '/Temporary local problem, please try again!/' => self::BOUNCE_REASON_TEMPORARY,
        '/4\.7\.3 Organization queue quota exceeded/' => self::BOUNCE_REASON_TEMPORARY,
        '/Temporary lookup failure/' => self::BOUNCE_REASON_TEMPORARY,
        '/OFR004_104/' => self::BOUNCE_REASON_TEMPORARY,
        '/All server ports are busy/' => self::BOUNCE_REASON_TEMPORARY,
        '/Service currently unavailable/' => self::BOUNCE_REASON_TEMPORARY,
        '/Try later/' => self::BOUNCE_REASON_TEMPORARY,
        '/deferred: please try again/' => self::BOUNCE_REASON_TEMPORARY,
        '/Too many SMTP connections from this host/' => self::BOUNCE_REASON_TEMPORARY,
        '/command timeout/' => self::BOUNCE_REASON_TEMPORARY,
        '/Timeout - closing connection/' => self::BOUNCE_REASON_TEMPORARY,
        // To refine
        '/kundenserver.de Service closing transmission channel - command timeout/' => self::BOUNCE_REASON_UNKNOWN,
        '/^quitting$/' => self::BOUNCE_REASON_UNKNOWN,
        '/Local Error/' => self::BOUNCE_REASON_UNKNOWN,
        '/Message delivery error/' => self::BOUNCE_REASON_UNKNOWN,
        '/Too old/' => self::BOUNCE_REASON_UNKNOWN,
        '/relay not permitted/' => self::BOUNCE_REASON_UNKNOWN,
        '/Relais interdit !/' => self::BOUNCE_REASON_UNKNOWN,
        '/Relay access denied/' => self::BOUNCE_REASON_UNKNOWN,
        '/5\.7\.1 Relaying denied/' => self::BOUNCE_REASON_UNKNOWN,
        '/5\.1\.0 Address rejected/' => self::BOUNCE_REASON_UNKNOWN,
        '/connection timeout/' => self::BOUNCE_REASON_UNKNOWN,
        '/Connection read error/' => self::BOUNCE_REASON_UNKNOWN,
        '/challenge asking for verification/' => self::BOUNCE_REASON_UNKNOWN,
        '/Message not delivered/' => self::BOUNCE_REASON_UNKNOWN,
        '/is not permitted to relay through this server without authentication/' => self::BOUNCE_REASON_UNKNOWN,
        '/Subject contains invalid characters/' => self::BOUNCE_REASON_UNKNOWN,
        '/message header size exceeds limit/' => self::BOUNCE_REASON_UNKNOWN,
    ];

    public function resolve(string $bounceReason): string
    {
        foreach (self::BOUNCE_REASONS_MAPPING as $pattern => $type) {
            if (preg_match($pattern, $bounceReason)) {
                return $type;
            }
        }
        return self::BOUNCE_REASON_UNQUALIFIED;
    }
}