<?php

namespace TestAssignment\src;

interface ProductValidatorInterface
{
    public function validatePostRequest(array $data): array;
    public function validateDeleteRequest(array $data): array;
    public function sanitize(array $data): array;
}
