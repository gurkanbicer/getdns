[!["GetDNS"](https://www.getdns.com.tr/images/getdns-logo-dark.svg)](https://www.getdns.com.tr)


# GetDNS

GetDNS is a parser for the `dig` command results. This package includes features for querying DNS records by using random public dns servers and for querying domain nameservers.

Example usage:

```
use Gurkanbicer\Getdns\Getdns;
use Gurkanbicer\Getdns\Exceptions\EmptyResponseException;
use Gurkanbicer\Getdns\Exceptions\InvalidDomainException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Exception\ProcessFailedException;

$getdns = new Getdns();

try {
    $getdns->setDomain("grkn.co");
    $aRecords = $getdns->queryDns("A");
    var_dump($aRecords);

    $domainNameservers = $getdns->queryDomainNameservers();
    var_dump($domainNameservers);
} catch (ProcessFailedException) {
    echo "The process has been failed.";
} catch (ProcessTimedOutException) {
    echo "The process has been timed out.";
}
```

Example output:

```
array(5) {
  ["status"]=>
  string(7) "NOERROR"
  ["data"]=>
  array(2) {
    [0]=>
    string(12) "188.114.97.3"
    [1]=>
    string(12) "188.114.96.3"
  }
  ["query"]=>
  string(1) "A"
  ["nameserver"]=>
  string(7) "9.9.9.9"
  ["command"]=>
  string(65) "dig +nocmd +noall +multiline +answer +comments grkn.co A @9.9.9.9"
}
```

## Installation

You can install it via Composer: `composer require gurkanbicer/getdns`

## Requirements

- PHP >= 8.0.2
- The **dig command** should be run on your server, otherwise you should install the package (for installation: [https://command-not-found.com/dig](https://command-not-found.com/dig))
- Your php environment shouldn't be disable proc_open function and other functions that starts with proc_*

## Supported DNS Types

- NS
- SOA
- A
- AAAA
- CNAME
- MX
- TXT
- SRV
- Actual domain nameservers (that the onwer pointed)

## Donations

If you wanna support me you can

[!["Buy Me A Coffee"](https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png)](https://www.buymeacoffee.com/gurkanbicer)

