<?php

namespace Danilopietrocampagnolo\LaravelVatValidation\Exceptions;

class ViesServiceException extends VatValidationException
{
  protected int $httpStatus;

  public function __construct(string $message = "", int $httpStatus = 0, \Throwable $previous = null)
  {
    parent::__construct($message, 0, $previous);
    $this->httpStatus = $httpStatus;
  }

  public function getHttpStatus(): int
  {
    return $this->httpStatus;
  }
}