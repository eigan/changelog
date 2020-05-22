<?php

declare(strict_types=1);

namespace Logg\Tests\Integration;

use Logg\GitRepository;
use PHPUnit\Framework\TestCase;

class GitRepositoryTest extends TestCase
{
    /**
     * @var GitRepository
     */
    private $repository;

    protected function setUp(): void
    {
        $this->repository = new GitRepository(__DIR__.'/../../');
    }

    public function testGetLastCommitAuthor()
    {
        return static::assertNotEmpty($this->repository->getLastCommitAuthor());
    }

    public function testGetLastCommitMessage()
    {
        return static::assertNotEmpty($this->repository->getLastCommitMessage());
    }
}
