# Hashcash Captcha Bundle

This bundle is a local alternative to Google's Recaptcha and FriendlyCaptcha using a proof-of-work approach with the hashcash algorithm. 
This bundle is inspired by https://github.com/jlopp/hashcash-form-protect
The js file calculates a hashcash token from a hash which is then validated by the server.

Defaults:
- stamp expire: 10 min

## Installation
```shell
composer require pimcorecasts/hash-cash-captcha-bundle
```

## Activation
Add in your `bundles.php`
```php
Pimcorecasts\Bundle\HashCash\HashCashBundle::class => [ 'all' => true ],
```

## Usage

### 1. Include the `.js` file
```php
<script src="/bundles/hashcash/js/hashCash.js">
```

### 2. Add class to your `submit` button
class: `.pchc-form`

### 3. Add in your Form the HashCash inputs
```php
{% for inputKey, inputName in pc_hash_cash().createStamp() %}
    <input type="hidden" name="{{ inputKey }}" value="{{ inputName }}"></input>
{% endfor %}
```

### 4. Validate the Hash on Submit on the Server side
Validate first your mandatory fields and at the end you can verify the hashCash.
```php
$validHashCash = $hashCashService->validateHashcashCaptcha();
```

### 5. Show error flash messages
This Helper takes the `currentRequest` and search for the HashCash fields.  
Then there runs the validation and if there is an error you get all Error's as `Flash Message` back.
```php
{% if app.session.flashBag.get('pchc_error') %}
    <ul>
    {% for message in  app.session.flashBag.get('pchc_error') %}
        <li>{{ message }}</li>
    {% endfor %}
    </ul>
{% endif %}
```

### Custom Result Handling Event
```js
document.addEventListener('hashcashFormValid', (event) => {
    
    // Disable automatic form submit
    event.detail.submitForm = false

    // custom checks if form should submit
    event.detail.valid = false
    
})
```


## Flash Messages for Error handling
- typ: pchc_error

### Messages:
**If the `checkStamp` gets an Error**
- pchc.invalid.try-again
---
**If Stamp is expired**
- pchc.stamp.expired
---
**If Puzzle is Expired (Server Side)**
- pchc.puzzle-expired
---
**If difficulty is to high or there is an error:**
- pchc.generic-error
---
**If the Hash key is already used to send a form:**
- pchc.puzzle-is-already-used


