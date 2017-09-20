<?php

namespace Logg\Remotes;

use Logg\Entry\Entry;
use Symfony\Component\Console\Style\OutputStyle;

class GitlabRemote implements IRemote
{
    /**
     * @var string
     */
    private $token;
    
    /**
     * @var string
     */
    private $remoteUrl;

    /**
     * @var string
     */
    private $project;
    
    /**
     * GitlabRemote constructor.
     *
     * @param string $token
     * @param string $remoteUrl (host)
     * @param string $project   Everything after : in remote url, excluding .git
     */
    public function __construct(string $token, string $remoteUrl, string $project)
    {
        $this->token = $token;
        
        $this->remoteUrl = $remoteUrl;
        $this->project = $project;
    }

    /**
     * Add properties to entry from api request
     * Usually required the reference to be set
     *
     * @param Entry $entry
     */
    public function decorate(Entry $entry)
    {
        $merge = $this->getMergeRequest($entry->getReference());
        
        foreach ($merge['labels'] as $label) {
            if (in_array($label, Entry::TYPES)) {
                $entry->setType($label);
            }
        }
        
        if (isset($merge['title'])) {
            $entry->setTitle($merge['title']);
        }
    }

    /**
     * Provide option for specify the merge request id
     *
     * @param OutputStyle $output
     * @param string|null $default
     *
     * @return mixed
     */
    public function askForReference(OutputStyle $output, string $default = null)
    {
        return $output->ask('Merge request ID', $default, function ($typed) {
            $reference = (int) $typed;
            
            return $reference > 0 ? $reference : '';
        });
    }

    /**
     * Standard way of representing the reference
     *
     * @param Entry $entry
     *
     * @return string
     */
    public function getReferenceText(Entry $entry): string
    {
        return '!' . $entry->getReference();
    }

    public function getReferenceUrl(Entry $entry): ?string
    {
        return '';
    }
    
    private function getMergeRequest(string $reference)
    {
        $curl = $this->makeApiRequest();
        
        curl_setopt($curl, CURLOPT_URL, $this->getApiUrl() . '/merge_requests/' . $reference . '?private_token=' . $this->token);
        $server_output = curl_exec($curl);
        
        return json_decode($server_output, true);
    }
    
    private function makeApiRequest()
    {
        $curl = curl_init();
        
        curl_setopt($curl, CURLOPT_POST, 0);
        
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_USERAGENT => 'Codular Sample cURL Request'
        ]);
        
        return $curl;
    }
    
    private function getApiUrl()
    {
        return 'https://' . $this->remoteUrl . '/api/v4/projects/' . str_replace('/', '%2F', $this->project);
    }
}
