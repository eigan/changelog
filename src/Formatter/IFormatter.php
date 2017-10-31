<?php

namespace Logg\Formatter;

interface IFormatter
{
    /**
     * @param string              $headline
     * @param \Logg\Entry\Entry[] $entries
     * @param array               $options
     *
     * @return mixed
     */
    public function format(string $headline, array $entries, array $options);
}
