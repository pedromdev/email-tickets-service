<?php

namespace App\Entity;

use App\Repository\MessageReceivedRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;


/**
 * @ORM\Entity(repositoryClass=MessageReceivedRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class MessageReceived
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="text")
     */
    private $body;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $contract_id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $processed_at;

    /**
     * @ORM\OneToMany(targetEntity=MessageReceivedAttachment::class, mappedBy="message_received", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
     */
    private $messageReceivedAttachments;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $thread_id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    private $sent_at;

    public function __construct(array $data = [])
    {
        $this->messageReceivedAttachments = new ArrayCollection();

        if (isset($data['id'])) {
            $this->id = $data['id'];
        }

        if (!empty($data)) {
            $this->setName($data['name'])
                ->setEmail($data['email'])
                ->setBody($data['body'])
                ->setSentAt($data['sent_at'])
                ->setThreadId($data['thread_id'])
                ->setContractId($data['contract_id']);

            foreach ($data['attachments'] as $attachment) {
                $this->addMessageReceivedAttachment(new MessageReceivedAttachment($attachment));
            }
        }
    }

    public function addMessageReceivedAttachment(MessageReceivedAttachment $messageReceivedAttachment): self
    {
        if (!$this->messageReceivedAttachments->contains($messageReceivedAttachment)) {
            $this->messageReceivedAttachments[] = $messageReceivedAttachment;
            $messageReceivedAttachment->setMessageReceived($this);
        }

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function initializeCreatedAt(): void
    {
        if ($this->created_at === null) {
            $this->created_at = new \DateTime('now');
        }
    }

    public function getProcessedAt(): ?\DateTimeInterface
    {
        return $this->processed_at;
    }

    public function setProcessedAt(?\DateTimeInterface $processed_at): self
    {
        $this->processed_at = $processed_at;

        return $this;
    }

    public function removeMessageReceivedAttachment(MessageReceivedAttachment $messageReceivedAttachment): self
    {
        if ($this->messageReceivedAttachments->contains($messageReceivedAttachment)) {
            $this->messageReceivedAttachments->removeElement($messageReceivedAttachment);
            // set the owning side to null (unless already changed)
            if ($messageReceivedAttachment->getMessageReceived() === $this) {
                $messageReceivedAttachment->setMessageReceived(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function toFormDataPart(): FormDataPart
    {
        return new FormDataPart([
            'id' => (string)$this->getId(),
            'name' => $this->getName(),
            'email' => $this->getEmail(),
            'body' => $this->getBody(),
            'thread_id' => $this->getThreadId(),
            'contract_id' => $this->getContractId(),
            'sent_at' => $this->getSentAt()->format('c'),
            'attachments' => array_map(function (MessageReceivedAttachment $attachment) {
                return $attachment->toFormData();
            }, $this->getMessageReceivedAttachments()->toArray()),
        ]);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getThreadId(): ?string
    {
        return $this->thread_id;
    }

    public function setThreadId(string $thread_id): self
    {
        $this->thread_id = $thread_id;

        return $this;
    }

    public function getContractId(): ?string
    {
        return $this->contract_id;
    }

    public function setContractId(string $contract_id): self
    {
        $this->contract_id = $contract_id;

        return $this;
    }

    public function getSentAt(): ?\DateTimeInterface
    {
        return $this->sent_at;
    }

    public function setSentAt(\DateTimeInterface $sent_at): self
    {
        $this->sent_at = $sent_at;

        return $this;
    }

    /**
     * @return Collection|MessageReceivedAttachment[]
     */
    public function getMessageReceivedAttachments(): Collection
    {
        return $this->messageReceivedAttachments;
    }
}
