<?php

namespace Gurkanbicer\Getdns;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Gurkanbicer\Getdns\Exceptions\InvalidDomainException;
use Gurkanbicer\Getdns\Exceptions\InvalidQueryTypeException;
use Gurkanbicer\Getdns\Exceptions\EmptyResponseException;

class Getdns
{
    private string|null $domain = null;
    private string|null $tld = null;
    private string|null $nameserver = null;
    private array $digAuthorityCmd = [];
    private array $digAnswerCmd = [];
    private array $digRootCmd = [];
    private string $domainRegex = "";
    private string $srvHostnameRegex = "";
    private array $rootServers = [];
    private array $queryTypes = [
        'A',
        'AAAA',
        'CNAME',
        'MX',
        'NS',
        'SOA',
        'SRV',
        'TXT'
    ];
    private array $dnsServers = [
        '1.1.1.1', // Cloudflare
        '1.0.0.1', // Cloudflare
        '8.8.8.8', // Google
        '8.8.4.4', // Google
        '9.9.9.9', // Quad9
        '149.112.112.112', // Quad9
    ];
    private string $defaultDnsServer = "8.8.8.8";

    public function __construct()
    {
        $this->domainRegex = "(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]";
        $this->srvHostnameRegex = "(_[a-zA-Z0-9]+)\.(_[a-zA-Z0-9]+)\.([a-zA-Z0-9.-]+)";
    }


    private function cleanDot($arr): array
    {
        $cleanArr = [];

        foreach ($arr as $key => $val) {
            $cleanKey = rtrim($key, '.');

            if (is_array($val)) {
                $cleanVal = $this->cleanDot($val);
            } else {
                $cleanVal = rtrim($val, '.');
            }

            $cleanArr[$cleanKey] = $cleanVal;
        }

        return $cleanArr;
    }

    /**
     * Get domain name
     * 
     * @return string|null
     */
    public function getDomain(): string|null
    {
        return $this->domain;
    }

    /**
     * Set domain name and TLD
     * 
     * @param string $domain
     * @return void
     */
    public function setDomain(string $domain): void
    {
        $this->domain = $domain;
        $tld = explode(".", $domain);
        $this->tld = end($tld);
    }

    /**
     * Get TLD
     * 
     * @return string|null
     */
    public function getTLD(): string|null
    {
        return $this->tld;
    }

    /**
     * Get nameserver for lookup
     * 
     * @return string|null
     */
    public function getNameserver(): string|null
    {
        return $this->nameserver;
    }

    /**
     * Set nameserver for lookup
     * 
     * @param string $nameserver
     * @return void
     */
    public function setNameserver(string $nameserver): void
    {
        $this->nameserver = $nameserver;
    }

    /**
     * Get DNS Servers
     * 
     * @return array
     */
    public function getDnsServers(): array
    {
        return $this->dnsServers;
    }

    /**
     * Set DNS Servers
     * 
     * @param array $dnsServers
     * @return void
     */
    public function setDnsServers(array $dnsServers): void
    {
        $this->dnsServers = $dnsServers;
    }

    /**
     * Get random DNS server
     * 
     * @return string
     */
    public function setRandomDnsServer(): void
    {
        $servers = $this->getDnsServers();

        if (empty($servers)) {
            $this->nameserver = $this->defaultDnsServer;
        } else {
            shuffle($servers);
            $this->nameserver = array_shift($servers);
        }
    }

    /**
     * Test domain name
     * 
     * @return void
     * @throws InvalidDomainException
     */
    private function testDomain(): void
    {
        if (!isset($this->domain) || $this->domain == "") {
            throw new InvalidDomainException("Domain name is required.");
        }

        if (!preg_match("/^{$this->domainRegex}$/", $this->domain)) {
            throw new InvalidDomainException("Invalid domain name.");
        }
    }

    /**
     * Test Hostname
     * 
     * @param bool $isSrv
     * @return void
     * @throws InvalidDomainException
     */
    private function testHostname(bool $isSrv = false): void
    {
        if ($isSrv) {
            if (!isset($this->domain) || $this->domain == "") {
                throw new InvalidDomainException("Domain name is required.");
            }

            if (!preg_match("/^{$this->srvHostnameRegex}$/", $this->domain)) {
                throw new InvalidDomainException("Invalid hostname for SRV record.");
            }
        } else {
            $this->testDomain();
        }
    }

    /**
     * Get dig command for authority servers
     * 
     * @param bool $asArray
     * @return array|string
     */
    private function getDigAuthorityCmd(bool $asArray = true): array|string
    {
        return ($asArray) ? $this->digAuthorityCmd : implode(" ", $this->digAuthorityCmd);
    }

    /**
     * Set dig command for authority servers
     * 
     * @param string $host
     * @param string $type
     * @param string|null $ns
     * @return void
     */
    private function setDigAuthorityCmd(string $host, string $type, string|null $ns): void
    {
        $cmd = "dig +nocmd +noall +multiline +authority +additional +comments %s %s %s";
        $cmd = sprintf($cmd, $host, $type, $ns ? "@{$ns}" : "");
        $cmd = trim(str_ireplace("  ", " ", $cmd));

        $this->digAuthorityCmd = explode(" ", $cmd);
    }

    /**
     * Get dig command for answer
     * 
     * @param bool $asArray
     * @return array|string
     */
    private function getDigAnswerCmd(bool $asArray = true): array|string
    {
        return ($asArray) ? $this->digAnswerCmd : implode(" ", $this->digAnswerCmd);
    }

    /**
     * Set dig command for answer
     * 
     * @param string $host
     * @param string $type
     * @param string|null $ns
     * @return void
     */
    private function setDigAnswerCmd(string $host, string $type, string|null $ns): void
    {
        $cmd = "dig +nocmd +noall +multiline +answer +comments %s %s %s";
        $cmd = sprintf($cmd, $host, $type, $ns ? "@{$ns}" : "");
        $cmd = trim(str_ireplace("  ", " ", $cmd));

        $this->digAnswerCmd = explode(" ", $cmd);
    }

    /**
     * Get dig command for root servers
     * 
     * @param bool $asArray
     * @return array|string
     */
    private function getDigRootCmd(bool $asArray = true): array|string
    {
        return ($asArray) ? $this->digRootCmd : implode(" ", $this->digRootCmd);
    }

    /**
     * Set dig command for root servers
     * 
     * @param string $host
     * @return void
     */
    private function setDigRootCmd(string $host): void
    {
        $cmd = "dig +nocmd +noall +multiline +answer +short NS %s";
        $cmd = sprintf($cmd, $host);
        $cmd = trim(str_ireplace("  ", " ", $cmd));

        $this->digRootCmd = explode(" ", $cmd);
    }

    /**
     * Query the root servers for the domain
     * 
     * @param int $timeout
     * @return void
     * @throws InvalidDomainException|EmptyResponseException|ProcessFailedException|ProcessTimedOutException
     */
    public function setRootServers(float $timeout = 1): void
    {
        $this->testDomain();
        $this->setDigRootCmd($this->tld);

        $process = new Process($this->getDigRootCmd());
        $process->setTimeout($timeout);

        $process->run();

        $processResults = $process->getOutput();

        if ($processResults == "") {
            throw new EmptyResponseException();
        }

        $explodedResults = explode("\n", $processResults);

        $results = [];

        foreach ($explodedResults as $item) {
            if ($item != '')
                $results[] = rtrim($item, '.');
        }

        shuffle($results);

        $this->rootServers = $results;
    }

    /**
     * Get root servers
     * 
     * @return array
     */
    public function getRootServers(): array
    {
        return $this->rootServers;
    }

    /**
     * Get answers from dig output
     * 
     * @param string $output
     * @return string
     */
    private function getAnswers(string $output): string
    {
        $output = preg_replace('/;;.*(\r?\n)?/', '', $output);
        return $output;
    }

    /**
     * Get comments from dig output

     * @param string $output
     * @return string|null
     */
    private function getComments(string $output): string|null
    {
        preg_match_all('/;;.*(\r?\n)?/', $output, $queryComments);
        return isset($queryComments[0]) ? implode('', $queryComments[0]) : null;
    }

    /**
     * Get status from dig output comments

     * @param string $output
     * @return string|null
     */
    private function getStatus(string $output): string|null
    {
        preg_match('/status:\s*(\S+),/', $output, $queryStatus);
        return isset($queryStatus[1]) ? $queryStatus[1] : null;
    }

    /**
     * Query domain nameservers
     * 
     * @param float $timeout
     * @throws InvalidDomainException|EmptyResponseException|ProcessFailedException|ProcessTimedOutException
     * @return array
     */
    public function queryDomainNameservers(float $timeout = 1)
    {
        $this->testDomain();

        if (empty($this->rootServers)) {
            $this->setRootServers($timeout);
        }

        $firstRootServer = array_shift($this->rootServers);

        $this->setDigAuthorityCmd($this->domain, "NS", $firstRootServer);

        $process = new Process($this->getDigAuthorityCmd());
        $process->setTimeout($timeout);
        $process->run();

        $processResults = $process->getOutput();

        if ($processResults == "") {
            throw new EmptyResponseException();
        }

        $queryComments = $this->getComments($processResults);

        if (is_null($queryComments)) {
            throw new EmptyResponseException();
        }

        $queryAnswers = $this->getAnswers($processResults);

        if ($queryAnswers == "") {
            throw new EmptyResponseException();
        }

        $queryStatus = $this->getStatus($queryComments);

        if (is_null($queryStatus)) {
            throw new EmptyResponseException();
        }

        if ($queryStatus != "NOERROR") {
            return [
                "status" => $queryStatus,
                "data" => [],
                "query" => "AuthorityServers",
                "nameserver" => $firstRootServer
            ];
        }

        $records = [];

        preg_match_all('/(\S+)\s+\d+\s+IN\s+NS\s+(\S+)/', $queryAnswers, $nsMatches);

        foreach ($nsMatches[2] as $ns) {
            preg_match_all('/' . preg_quote($ns) . '\s+\d+\s+IN\s+A\s+(\S+)/', $queryAnswers, $aMatches);

            if (isset($aMatches[1])) {
                $records[rtrim($ns, ".")] = $aMatches[1];
            } else {
                $records[rtrim($ns, ".")] = [];
            }
        }

        $cleanRecords = $this->cleanDot($records);

        return [
            "status" => $queryStatus,
            "data" => $cleanRecords,
            "query" => "AuthorityServers",
            "nameserver" => $firstRootServer,
            "command" => [
                0 => $this->getDigRootCmd(false),
                1 => $this->getDigAuthorityCmd(false)
            ]
        ];
    }

    /**
     * Query DNS
     * 
     * @param string $type
     * @param float $timeout
     * @throws InvalidDomainException|EmptyResponseException|ProcessFailedException|ProcessTimedOutException
     * @return array
     */
    public function queryDns(string $type = "A", float $timeout = 1)
    {
        $this->testHostname($type == "SRV");

        if (!in_array($type, $this->queryTypes)) {
            throw new InvalidQueryTypeException();
        }

        $this->setDigAnswerCmd($this->domain, $type, $this->nameserver);

        $process = new Process($this->getDigAnswerCmd());
        $process->setTimeout($timeout);
        $process->run();

        $processResults = $process->getOutput();

        if (strlen($processResults) < 10) {
            throw new EmptyResponseException();
        }

        $queryComments = $this->getComments($processResults);

        if (strlen($queryComments) < 10) {
            throw new EmptyResponseException();
        }

        $queryAnswers = $this->getAnswers($processResults);

        if (strlen($queryAnswers) < 10) {
            throw new EmptyResponseException();
        }

        $queryStatus = $this->getStatus($queryComments);

        if (is_null($queryStatus)) {
            throw new EmptyResponseException();
        }

        if ($queryStatus != "NOERROR") {
            return [
                "status" => $queryStatus,
                "data" => [],
                "query" => $type,
                "nameserver" => $this->nameserver
            ];
        }

        $records = [];

        switch ($type) {
            case 'NS':
                preg_match_all('/\S+\s+\d+\s+IN\s+NS\s+(\S+)/', $queryAnswers, $matches);
                $records = isset($matches[1]) ? $matches[1] : [];
                break;
            case 'A':
                preg_match_all('/\S+\s+\d+\s+IN\s+A\s+(\S+)/', $queryAnswers, $matches);
                $records = isset($matches[1]) ? $matches[1] : [];
                break;
            case 'AAAA':
                preg_match_all('/\S+\s+\d+\s+IN\s+AAAA\s+(\S+)/', $queryAnswers, $matches);
                $records = isset($matches[1]) ? $matches[1] : [];
                break;
            case 'CNAME':
                preg_match_all('/\S+\s+\d+\s+IN\s+CNAME\s+(\S+)/', $queryAnswers, $matches);
                $records = isset($matches[1]) ? $matches[1] : [];
                break;
            case 'MX':
                preg_match_all('/\S+\s+\d+\s+IN\s+MX\s+(\d+)\s+(\S+)/', $queryAnswers, $matches);
                foreach ($matches[2] as $index => $hostname) {
                    $records[$hostname] = [
                        'priority' => $matches[1][$index],
                        'ips' => []
                    ];
                }
            case 'SOA':
                $lines = explode("\n", $queryAnswers);

                preg_match('/(\S+)\s+\d+\s+IN\s+SOA\s+(\S+)\s+(\S+)/', trim($lines[1]), $matches);
                if ($matches) {
                    $records['domain'] = $matches[1];
                    $records['primary_ns'] = $matches[2];
                    $records['responsible_email'] = $matches[3];
                }

                foreach ($lines as $line) {
                    $line = trim($line);

                    if (strpos($line, 'serial') !== false) {
                        $records['serial'] = (int) filter_var($line, FILTER_SANITIZE_NUMBER_INT);
                    }

                    if (strpos($line, 'refresh') !== false) {
                        $records['refresh'] = (int) filter_var($line, FILTER_SANITIZE_NUMBER_INT);
                    }

                    if (strpos($line, 'retry') !== false) {
                        $records['retry'] = (int) filter_var($line, FILTER_SANITIZE_NUMBER_INT);
                    }

                    if (strpos($line, 'expire') !== false) {
                        $records['expire'] = (int) filter_var($line, FILTER_SANITIZE_NUMBER_INT);
                    }

                    if (strpos($line, 'minimum') !== false) {
                        $records['minimum'] = (int) filter_var($line, FILTER_SANITIZE_NUMBER_INT);
                    }
                }
                break;
            case 'TXT':
                preg_match_all('/\S+\s+\d+\s+IN\s+TXT\s+"([^"]+)"/', $queryAnswers, $matches);
                $records = isset($matches[1]) ? $matches[1] : [];
                break;
            case 'SRV':
                preg_match('/(\S+)\s+\d+\s+IN\s+SRV\s+(\d+)\s+(\d+)\s+(\d+)\s+(\S+)/', $queryAnswers, $matches);
                if ($matches) {
                    $records = [
                        'service' => $matches[1],
                        'priority' => $matches[2],
                        'weight' => $matches[3],
                        'port' => $matches[4],
                        'target' => $matches[5]
                    ];
                }
                break;
        }

        $cleanRecords = $this->cleanDot($records);

        return [
            "status" => $queryStatus,
            "data" => $cleanRecords,
            "query" => $type,
            "nameserver" => $this->nameserver,
            "command" => $this->getDigAnswerCmd(false)
        ];
    }
}
