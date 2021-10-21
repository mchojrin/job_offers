<?php

namespace App\Entity;

class Campaign
{
    private string $id;
    private string $contents;

    /**
     * @return string
     */
    public function getContents(): string
    {
        return $this->contents;
    }

    /**
     * @param string $contents
     * @return Campaign
     */
    public function setContents(string $contents): Campaign
    {
        $this->contents = $contents;
        return $this;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return Campaign
     */
    public function setId(string $id): Campaign
    {
        $this->id = $id;
        return $this;
    }
}