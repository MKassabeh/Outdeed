<?php

namespace App\Entity;

use App\Repository\SkillRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SkillRepository::class)]
class Skill
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 100)]
    private $skill_name;

    #[ORM\Column(type: 'string', length: 100)]
    private $years_of_experience;

    #[ORM\ManyToOne(targetEntity: Candidate::class, inversedBy: 'skills')]
    private $candidate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSkillName(): ?string
    {
        return $this->skill_name;
    }

    public function setSkillName(string $skill_name): self
    {
        $this->skill_name = $skill_name;

        return $this;
    }

    public function getYearsOfExperience(): ?string
    {
        return $this->years_of_experience;
    }

    public function setYearsOfExperience(string $years_of_experience): self
    {
        $this->years_of_experience = $years_of_experience;

        return $this;
    }

    public function getCandidate(): ?Candidate
    {
        return $this->candidate;
    }

    public function setCandidate(?Candidate $candidate): self
    {
        $this->candidate = $candidate;

        return $this;
    }
}
