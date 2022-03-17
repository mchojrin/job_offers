<?php

namespace App\Entity;

class JobOffer
{
    private string $description;
    private string $jobType;
    private bool $remoteAvailable;
    private string $compensation;
    private string $contact;
    private string $required;
    private string $optional;
    private string $benefits;
    private string $misc;
    private \DateTimeImmutable $date;
    private ?\DateTimeImmutable $sent;

    /**
     * @return \DateTimeImmutable
     */
    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @param \DateTimeImmutable $date
     * @return JobOffer
     */
    public function setDate(\DateTimeImmutable $date): JobOffer
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return JobOffer
     */
    public function setDescription(string $description): JobOffer
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getJobType(): string
    {
        return $this->jobType;
    }

    /**
     * @param string $jobType
     * @return JobOffer
     */
    public function setJobType(string $jobType): JobOffer
    {
        $this->jobType = $jobType;
        return $this;
    }

    /**
     * @return bool
     */
    public function isRemoteAvailable(): bool
    {
        return $this->remoteAvailable;
    }

    /**
     * @param bool $remoteAvailable
     * @return JobOffer
     */
    public function setRemoteAvailable(bool $remoteAvailable): JobOffer
    {
        $this->remoteAvailable = $remoteAvailable;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompensation(): string
    {
        return $this->compensation;
    }

    /**
     * @param string $compensation
     * @return JobOffer
     */
    public function setCompensation(string $compensation): JobOffer
    {
        $this->compensation = $compensation;
        return $this;
    }

    /**
     * @return string
     */
    public function getContact(): string
    {
        return $this->contact;
    }

    /**
     * @param string $contact
     * @return JobOffer
     */
    public function setContact(string $contact): JobOffer
    {
        $this->contact = $contact;
        return $this;
    }

    /**
     * @return string
     */
    public function getRequired(): string
    {
        return $this->required;
    }

    /**
     * @param string $required
     * @return JobOffer
     */
    public function setRequired(string $required): JobOffer
    {
        $this->required = $required;
        return $this;
    }

    /**
     * @return string
     */
    public function getOptional(): string
    {
        return $this->optional;
    }

    /**
     * @param string $optional
     * @return JobOffer
     */
    public function setOptional(string $optional): JobOffer
    {
        $this->optional = $optional;
        return $this;
    }

    /**
     * @return string
     */
    public function getBenefits(): string
    {
        return $this->benefits;
    }

    /**
     * @param string $benefits
     * @return JobOffer
     */
    public function setBenefits(string $benefits): JobOffer
    {
        $this->benefits = $benefits;
        return $this;
    }

    /**
     * @return string
     */
    public function getMisc(): string
    {
        return $this->misc;
    }

    /**
     * @param string $misc
     * @return JobOffer
     */
    public function setMisc(string $misc): JobOffer
    {
        $this->misc = $misc;
        return $this;
    }

    /**
     * @return ?\DateTimeImmutable
     */
    public function getSent(): ?\DateTimeImmutable
    {
        return $this->sent;
    }

    /**
     * @param \DateTimeImmutable $sent
     */
    public function setSent(\DateTimeImmutable $sent = null): void
    {
        $this->sent = $sent;
    }
}