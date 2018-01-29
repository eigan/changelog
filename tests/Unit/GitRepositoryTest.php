<?php

namespace Logg\Tests\Integration;

use Logg\GitRepository;
use PHPUnit\Framework\TestCase;

class GitRepositoryTest extends TestCase
{
    /**
     * @var GitRepository
     */
    private $repository;
    
    public function setUp()
    {
        $this->repository = new GitRepository(__DIR__ . '/../../');
    }

    public function testGetLastCommitAuthor()
    {
        return $this->assertNotEmpty($this->repository->getLastCommitAuthor());
    }

    public function testGetLastCommitMessage()
    {
        return $this->assertNotEmpty($this->repository->getLastCommitMessage());
    }
}
