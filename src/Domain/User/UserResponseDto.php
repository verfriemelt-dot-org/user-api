<?php

    declare( strict_types = 1 );

    namespace App\Domain\User;

    use \App\DtoInterface;
    use \Symfony\Component\Validator\Constraints\NotBlank;

    class UserResponseDto
    implements DtoInterface {

        private ?int $id = null;

        #[ NotBlank ]
        private string $name;

        #[ NotBlank ]
        private string $lastname;

        public function __construct( ?int $id, string $name, string $lastname ) {

            $this->id       = $id;
            $this->name     = $name;
            $this->lastname = $lastname;
        }

        public function getId(): ?int {
            return $this->id;
        }

        public function getName(): string {
            return $this->name;
        }

        public function getLastname(): string {
            return $this->lastname;
        }

    }
