<?php

namespace Gurkanbicer\Getdns\Tests;

use Gurkanbicer\Getdns\Exceptions\EmptyResponseException;
use Gurkanbicer\Getdns\Exceptions\InvalidDomainException;
use Gurkanbicer\Getdns\Getdns;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Exception\ProcessFailedException;

require_once __DIR__ . "/../vendor/autoload.php";

class GetdnsTest extends TestCase
{

    public function testGetDomain(): void
    {
        $getdns = new Getdns();
        $getdns->setDomain("example.com");
        $this->assertEquals("example.com", $getdns->getDomain());

        var_dump($getdns->getDomain());
    }

    public function testGetTld(): void
    {
        $getdns = new Getdns();
        $getdns->setDomain("example.com.tr");
        $this->assertEquals("tr", $getdns->getTLD());

        var_dump($getdns->getTLD());
    }

    public function testGetRootServers(): void
    {
        $getdns = new Getdns();
        $getdns->setDomain("example.com");
        $getdns->setNameserver("1.1.1.1");

        $getdns->setRootServers();

        $rootServers = $getdns->getRootServers();

        if (is_array($rootServers)) {
            $this->assertTrue(true);
        }
        
        if (count($rootServers) > 0) {
            $this->assertTrue(true);
        }

        var_dump($rootServers);
    }

    public function testQueryDomainNameservers(): void
    {
        $domain = "grkn.co";

        $getdns = new Getdns();
        $getdns->setDomain($domain);

        $domainNameservers = $getdns->queryDomainNameservers();

        if (is_array($domainNameservers)) {
            $this->assertTrue(true);
        }

        if (isset($domainNameservers['status'])) {
            $this->assertTrue(true);
        }

        $this->assertEquals("NOERROR", $domainNameservers['status']);

        var_dump($domainNameservers);
    }

    public function testQueryDnsAType(): void
    {
        $domain = "grkn.co";

        $getdns = new Getdns();
        $getdns->setDomain($domain);
        $getdns->setRandomDnsServer();

        $records = $getdns->queryDns('A');

        if (is_array($records)) {
            $this->assertTrue(true);
        }

        if (isset($records['status'])) {
            $this->assertTrue(true);
        }

        $this->assertEquals("NOERROR", $records['status']);

        if (count($records['data']) > 0) {
            $this->assertTrue(true);
        }

        var_dump($records);
    }

    public function testQueryDnsCnameType(): void
    {
        $domain = "www.facebook.com";

        $getdns = new Getdns();
        $getdns->setDomain($domain);

        $records = $getdns->queryDns('CNAME');

        if (is_array($records)) {
            $this->assertTrue(true);
        }

        if (isset($records['status'])) {
            $this->assertTrue(true);
        }

        $this->assertEquals("NOERROR", $records['status']);

        if (count($records['data']) > 0) {
            $this->assertTrue(true);
        }

        var_dump($records);
    }

    public function testQueryDnsAaaaType(): void
    {
        $domain = "facebook.com";

        $getdns = new Getdns();
        $getdns->setDomain($domain);

        $records = $getdns->queryDns('AAAA');

        if (is_array($records)) {
            $this->assertTrue(true);
        }

        if (isset($records['status'])) {
            $this->assertTrue(true);
        }

        $this->assertEquals("NOERROR", $records['status']);

        if (count($records['data']) > 0) {
            $this->assertTrue(true);
        }

        var_dump($records);
    }

    public function testQueryDnsMxType(): void
    {
        $domain = "grkn.co";

        $getdns = new Getdns();
        $getdns->setDomain($domain);
        $getdns->setNameserver("1.1.1.1");

        $records = $getdns->queryDns('MX');

        if (is_array($records)) {
            $this->assertTrue(true);
        }

        if (isset($records['status'])) {
            $this->assertTrue(true);
        }

        $this->assertEquals("NOERROR", $records['status']);

        if (count($records['data']) > 0) {
            $this->assertTrue(true);
        }

        var_dump($records);
    }

    public function testQueryDnsTxtType(): void
    {
        $domain = "grkn.co";

        $getdns = new Getdns();
        $getdns->setDomain($domain);

        $records = $getdns->queryDns('TXT');

        if (is_array($records)) {
            $this->assertTrue(true);
        }

        if (isset($records['status'])) {
            $this->assertTrue(true);
        }

        $this->assertEquals("NOERROR", $records['status']);

        if (count($records['data']) > 0) {
            $this->assertTrue(true);
        }

        var_dump($records);
    }

    public function testQueryDnsSoaType(): void
    {
        $domain = "grkn.co";

        $getdns = new Getdns();
        $getdns->setDomain($domain);

        $records = $getdns->queryDns('SOA');

        if (is_array($records)) {
            $this->assertTrue(true);
        }

        if (isset($records['status'])) {
            $this->assertTrue(true);
        }

        $this->assertEquals("NOERROR", $records['status']);

        if (count($records['data']) > 0) {
            $this->assertTrue(true);
        }

        var_dump($records);
    }

    public function testQueryDnsNsType(): void
    {
        $domain = "grkn.co";

        $getdns = new Getdns();
        $getdns->setDomain($domain);

        $records = $getdns->queryDns('NS');

        if (is_array($records)) {
            $this->assertTrue(true);
        }

        if (isset($records['status'])) {
            $this->assertTrue(true);
        }

        $this->assertEquals("NOERROR", $records['status']);

        if (count($records['data']) > 0) {
            $this->assertTrue(true);
        }

        var_dump($records);
    }

    public function testQueryDnsSrvType(): void
    {
        $domain = "_sip._tcp.grkn.co";

        $getdns = new Getdns();
        $getdns->setDomain($domain);

        $records = $getdns->queryDns('SRV');

        if (is_array($records)) {
            $this->assertTrue(true);
        }

        if (isset($records['status'])) {
            $this->assertTrue(true);
        }

        $this->assertEquals("NOERROR", $records['status']);

        if (count($records['data']) > 0) {
            $this->assertTrue(true);
        }

        var_dump($records);
    }

    public function testInvalidDomainException(): void
    {
        try {
            $domain = "grkn";
        
            $getdns = new Getdns();
            $getdns->setDomain($domain);
            $getdns->queryDomainNameservers();

            echo "InvalidDomainException is not thrown.";
            $this->assertFalse(true);
        } catch (InvalidDomainException $e) {
            echo "InvalidDomainException is thrown.";
            $this->assertTrue(true);
        }
    }

    public function testTimeoutException(): void
    {
        try {
            $domain = "grkn.co";

            $getdns = new Getdns();
            $getdns->setDomain($domain);
            $getdns->queryDomainNameservers(0.0002);

            echo "ProcessTimedOutException is not thrown.";
            $this->assertFalse(true);
        } catch (ProcessTimedOutException $e) {
            echo "ProcessTimedOutException is thrown.";
            $this->assertTrue(true);
        }
    }

    public function testEmptyResponseException(): void
    {
        try {
            $domain = "www.grkn.co";

            $getdns = new Getdns();
            $getdns->setDomain($domain);
            $results = $getdns->queryDns('CNAME');
            var_dump($results);

            echo "EmptyResponseException is not thrown.";
            $this->assertFalse(true);
        } catch (EmptyResponseException $e) {
            echo "EmptyResponseException is thrown.";
            $this->assertTrue(true);
        }
    }
}
