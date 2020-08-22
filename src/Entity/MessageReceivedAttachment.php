<?php

namespace App\Entity;

use App\Repository\MessageReceivedAttachmentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Mime\Part\DataPart;

/**
 * @ORM\Entity(repositoryClass=MessageReceivedAttachmentRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class MessageReceivedAttachment
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
    private $attachment_id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $path;

    /**
     * @ORM\ManyToOne(targetEntity=MessageReceived::class, inversedBy="messageReceivedAttachments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $message_received;

    /**
     * MessageReceivedAttachment constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->setAttachmentId($data['attachment_id'])
                ->setName($data['name'])
                ->setPath($data['path']);
        }
    }

    /**
     * @ORM\PreRemove
     */
    public function removeAttachmentFile()
    {
        $filesystem = new Filesystem();
        $filesystem->remove($this->getPath());
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getMessageReceived(): ?MessageReceived
    {
        return $this->message_received;
    }

    public function setMessageReceived(?MessageReceived $message_received): self
    {
        $this->message_received = $message_received;

        return $this;
    }

    public function toFormData(): array
    {
        return [
            'id' => (string)$this->getId(),
            'attachment_id' => $this->getAttachmentId(),
            'name' => $this->getName(),
            'file' => DataPart::fromPath($this->getPath())
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAttachmentId(): ?string
    {
        return $this->attachment_id;
    }

    public function setAttachmentId(string $attachment_id): self
    {
        $this->attachment_id = $attachment_id;

        return $this;
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
}
