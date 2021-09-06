<?php

namespace App\GitHub;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GitHubApiHelper
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getOrganizationInfo(string $organization): GitHubOrganization
    {
        try {
            // FIXME: If we call Github API too much, we get a 403
            $response = $this->httpClient->request('GET', 'https://api.github.com/orgs/' . $organization);
            $data = $response->toArray();
        } catch (ClientExceptionInterface $e) {
            $data = [
                'name' => 'SymfonyCasts',
                'public_repos' => 1,
            ];
        }

        return new GitHubOrganization(
            $data['name'],
            $data['public_repos']
        );
    }

    /**
     * @return GitHubRepository[]
     */
    public function getOrganizationRepositories(string $organization): array
   {
       try {
           // FIXME: If we call Github API too much, we get a 403
           $response = $this->httpClient->request('GET', sprintf('https://api.github.com/orgs/%s/repos', $organization));
           $data = $response->toArray();
       } catch (ClientExceptionInterface $e) {
           $data = [];
       }

       $repositories = [];
       foreach ($data as $repoData) {
           $repositories[] = new GitHubRepository(
               $repoData['name'],
               $repoData['html_url'],
               \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s\Z', $repoData['updated_at'])
           );
       }

       return $repositories;
   }
}
