### Introduction
This bundle allows developers to encrypt its keys ans store them using redis as a vault

### Requirements
- This bundle requires [sodium extension](https://www.php.net/manual/en/book.sodium.php) and [predis](https://github.com/predis/predis) to be installed.

## Installation
Install it using composer:

```shell
composer require ict/secrets:dev-main
```

### Configuration

Bundle requires the following configuration:

```ỳaml
ict_secrets:
  hash_alg: 'sha512' # Any type supported by php hash function
  encoder: sodium
  store:
    type: redis
    config:
      uri: 'your redis uri connection'
```

At the moment, only sodium encoder and redis vault are allowed. 

### How to use it

#### Inject Vault service:

```php

use Ict\Secrets\Vault\VaultStorageInterface;

..........

public function __construct(    
    private readonly VaultStorageInterface $vaultStorage
)
```

#### Store a secret

```php
$this->vaultStorage->storeKeys('your_encription_keys_name')->storeSecret('your_secret_name', 'your_secret_value');
```
_your_encription_keys_name_ is used to store the encription keys by which your secrets will be encripted

### Retrieve a secret

```php
$secretVal = $this->vaultStorage->getSecret($secretKey)
```

### Remove a secret

```php
$secretVal = $this->vaultStorage->removeSecret($secretKey)
```
