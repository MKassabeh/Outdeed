<?php

namespace App\Entity;

use App\Repository\CandidateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CandidateRepository::class)]
class Candidate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private $phone_number;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $city;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private $first_name;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private $last_name;

    #[ORM\OneToMany(mappedBy: 'candidate', targetEntity: Skill::class)]
    private $skills;

    #[ORM\OneToMany(mappedBy: 'candidate', targetEntity: ProfessionalExperience::class)]
    private $professional_experience;

    #[ORM\OneToOne(targetEntity: User::class, cascade: ['persist', 'remove'])]
    private $user;

    #[ORM\Column(type: 'string', length: 70, nullable: true)]
    private $address;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $birthdate;

    public function __construct()
    {
        $this->skills = new ArrayCollection();
        $this->professional_experience = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phone_number;
    }

    public function setPhoneNumber(?string $phone_number): self
    {
        $this->phone_number = $phone_number;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(?string $first_name): self
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(?string $last_name): self
    {
        $this->last_name = $last_name;

        return $this;
    }

    /**
     * @return Collection|Skill[]
     */
    public function getSkills(): Collection
    {
        return $this->skills;
    }

    public function addSkill(Skill $skill): self
    {
        if (!$this->skills->contains($skill)) {
            $this->skills[] = $skill;
            $skill->setCandidate($this);
        }

        return $this;
    }

    public function removeSkill(Skill $skill): self
    {
        if ($this->skills->removeElement($skill)) {
            // set the owning side to null (unless already changed)
            if ($skill->getCandidate() === $this) {
                $skill->setCandidate(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ProfessionalExperience[]
     */
    public function getProfessionalExperience(): Collection
    {
        return $this->professional_experience;
    }

    public function addProfessionalExperience(ProfessionalExperience $professionalExperience): self
    {
        if (!$this->professional_experience->contains($professionalExperience)) {
            $this->professional_experience[] = $professionalExperience;
            $professionalExperience->setCandidate($this);
        }

        return $this;
    }

    public function removeProfessionalExperience(ProfessionalExperience $professionalExperience): self
    {
        if ($this->professional_experience->removeElement($professionalExperience)) {
            // set the owning side to null (unless already changed)
            if ($professionalExperience->getCandidate() === $this) {
                $professionalExperience->setCandidate(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTimeInterface $birthdate): self
    {
        $this->birthdate = $birthdate;

        return $this;
    }
}
