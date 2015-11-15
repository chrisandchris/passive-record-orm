# Encryption

The version *2.1.0* supports now encrypted database fields. The encryption is
done by using a separated input to decrypt the value when fetching rows and encrypting
values when inserting rows. The interface is separated, so the encryption service
does not care where you fetch the encryption key from, whether it's the database
itself, a http-server, a string input... You may just need to write your own executor.

Basically, there is a *EncryptionService* which gets a *EncryptionExecutor* and a *Entity*.
Based on those input, it decides whether it's responsible for that entity or not (event-driven).
If it is responsible, based on your coding it decrypts the input using the Executor.
An Executor itself is responsible for getting the keys in the right place and doing the encryption
or decryption on cipher or raw text. The executor is separated form the encryption service
so you may use executors to encrypt random data, not just entities.

The executors itself preferally use the *CryptoInterface* to get the encryption library.
We have built-in support for *phpseclib*, but you might want to use your own encryption
library.

This actually is right now a "proof-of-concept", we do not plan to change the API
but to extend and improve the handling to have a more automatised approach.

## Decryption

Decryption is done using the *EncryptionService* and the *Executor*.

```php
$encryptionService = new DefaultEncryptionService();
$executor = new StringBasedExecutor(new PhpSeclibAesWrapper(new AES()));
$executor->useKey('root', 'abc-def-def-efg-ahb');

$encryptionService->useForRow($executor);
```

By default `useForRow()` decrypts all fields, but you may give as a 2nd param a list
of fields which are ignored when decrypting.

## Encryption

Encryption is done right within the *Builder* by using the *Executor* only.

```php
$builder = $this->getBuilder();

$executor = new StringBasedExecutor(new PhpSeclibAesWrapper(new AES()));
$executor->useKey('root', 'abc-def-def-efg-ahb');

$query = $builder->useEncryptionService($executor)
                 ->encryptedValue('Mr. Jones')
                 ->getSqlQuery();
```
