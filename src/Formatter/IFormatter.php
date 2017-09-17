<?php

namespace Logg\Formatter;

interface IFormatter
{
    /**
     * @param string              $headline
     * @param \Logg\Entry\Entry[] $entries
     *
     * @return mixed
     */
    public function format(string $headline, array $entries);
}
