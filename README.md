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

### 2. Add class to your `form` tag
class: `.pchc-form`

### 3.a Add in your Form the HashCash inputs
```php
{% for inputKey, inputName in pc_hash_cash().createStamp() %}
    <input type="hidden" name="{{ inputKey }}" value="{{ inputName }}"></input>
{% endfor %}
```

### 3.b OR add ajax class to your `form` tag
class: `.pchc-form-ajax`

This will automatically add the input fields to your form via javascript and ajax request


### 4. Validate the Hash on Submit on the Server side
Validate first your mandatory fields and at the end you can verify the hashCash.
```php
$validHashCash = $hashCashService->validateHashcashCaptcha();
```

### 5. Show error flash messages
This Helper takes the `currentRequest` and search for the HashCash fields.  
Then there runs the validation and if there is an error you get all Error's as `Flash Message` back.
```twig
{% set pchcErrors = app.session.flashBag.get('pchc_error') %}
{% if pchcErrors is not empty %}
    <ul>
    {% for message in pchcErrors %}
        <li>{{ message }}</li>
    {% endfor %}
    </ul>
{% endif %}
```

## Configuration
You can edit the valid stamp time and the difficulty via setters in the template.
```twig
{# edit the difficulty #}
{% do pc_hash_cash().setHashcashDifficulty(15) %}

{# edit the time-range the stamp will be valid #}
{# increase for e.g. job application site #}
{% do pc_hash_cash().setHashcashTimeWindow(20) %}
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
- type: pchc_error

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


