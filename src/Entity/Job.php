<?php

namespace App\Entity;

use App\Repository\JobRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JobRepository::class)]
class Job
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 100)]
    private $title;

    #[ORM\Column(type: 'text')]
    private $description_company;

    #[ORM\Column(type: 'text')]
    private $description_job;

    #[ORM\Column(type: 'text')]
    private $description_applicant;

    #[ORM\Column(type: 'string', length: 100)]
    private $city;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private $wages;

    #[ORM\Column(type: 'string', length: 100)]
    private $contract_type;

    #[ORM\Column(type: 'datetime')]
    private $published_at;

    #[ORM\Column(type: 'text', nullable: true)]
    private $benefits;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $schedule;

    #[ORM\Column(type: 'text', nullable: true)]
    private $company_comment;

    #[ORM\Column(type: 'string', length: 50)]
    private $category;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'job_offers')]
    #[ORM\JoinColumn(nullable: false)]
    private $published_by;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }
    
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescriptionCompany(): ?string
    {
        return $this->description_company;
    }

    public function setDescriptionCompany(string $description_company): self
    {
        $this->description_company = $description_company;

        return $this;
    }

    public function getDescriptionJob(): ?string
    {
        return $this->description_job;
    }

    public function setDescriptionJob(string $description_job): self
    {
        $this->description_job = $description_job;

        return $this;
    }

    public function getDescriptionApplicant(): ?string
    {
        return $this->description_applicant;
    }

    public function setDescriptionApplicant(string $description_applicant): self
    {
        $this->description_applicant = $description_applicant;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getWages(): ?string
    {
        return $this->wages;
    }

    public function setWages(?string $wages): self
    {
        $this->wages = $wages;

        return $this;
    }

    public function getContractType(): ?string
    {
        return $this->contract_type;
    }

    public function setContractType(string $contract_type): self
    {
        $this->contract_type = $contract_type;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->published_at;
    }

    public function setPublishedAt(\DateTimeInterface $published_at): self
    {
        $this->published_at = $published_at;

        return $this;
    }

    public function getBenefits(): ?string
    {
        return $this->benefits;
    }

    public function setBenefits(?string $benefits): self
    {
        $this->benefits = $benefits;

        return $this;
    }

    public function getSchedule(): ?string
    {
        return $this->schedule;
    }

    public function setSchedule(?string $schedule): self
    {
        $this->schedule = $schedule;

        return $this;
    }

    public function getCompanyComment(): ?string
    {
        return $this->company_comment;
    }

    public function setCompanyComment(?string $company_comment): self
    {
        $this->company_comment = $company_comment;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getPublishedBy(): ?User
    {
        return $this->published_by;
    }

    public function setPublishedBy(?User $published_by): self
    {
        $this->published_by = $published_by;

        return $this;
    }
}
