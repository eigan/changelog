<?php

namespace Logg\Remotes;

use Logg\Entry\Entry;
use Symfony\Component\Console\Style\OutputStyle;

interface IRemote
{
    /**
     * Add properties to entry from api request
     * Usually required the reference to be set
     *
     * @param Entry $entry
     */
    public function decorate(Entry $entry);

    /**
     * Provide option for specify the merge request id
     *
     * @param OutputStyle $output
     * @param string|null $default
     *
     * @return mixed
     */
    public function askForReference(OutputStyle $output, string $default = null);

    /**
     * Standard way of representing the reference
     *
     * @param Entry $entry
     *
     * @return string
     */
    public function getReferenceText(Entry $entry): string;

    public function getReferenceUrl(Entry $entry): ?string;
}
