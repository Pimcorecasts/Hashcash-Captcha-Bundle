var pimcorecastsHashCash = {

    // full implementation of SHA265 hashing algorithm
    sha256: function (ascii) {
        var mathPow = Math.pow;
        var maxWord = mathPow(2, 32);
        var lengthProperty = 'length'
        var i, j; // Used as a counter across the whole file
        var result = ''

        var words = [];
        var asciiBitLength = ascii[lengthProperty] * 8;

        //* caching results is optional - remove/add slash from front of this line to toggle
        // Initial hash value: first 32 bits of the fractional parts of the square roots of the first 8 primes
        // (we actually calculate the first 64, but extra values are just ignored)
        var hash = pimcorecastsHashCash.sha256.h = pimcorecastsHashCash.sha256.h || [];
        // Round constants: first 32 bits of the fractional parts of the cube roots of the first 64 primes
        var k = pimcorecastsHashCash.sha256.k = pimcorecastsHashCash.sha256.k || [];
        var primeCounter = k[lengthProperty];

        var isComposite = {};
        for (var candidate = 2; primeCounter < 64; candidate++) {
            if (!isComposite[candidate]) {
                for (i = 0; i < 313; i += candidate) {
                    isComposite[i] = candidate;
                }
                hash[primeCounter] = (mathPow(candidate, .5) * maxWord) | 0;
                k[primeCounter++] = (mathPow(candidate, 1 / 3) * maxWord) | 0;
            }
        }

        ascii += '\x80' // Append Ƈ' bit (plus zero padding)
        while (ascii[lengthProperty] % 64 - 56) ascii += '\x00' // More zero padding
        for (i = 0; i < ascii[lengthProperty]; i++) {
            j = ascii.charCodeAt(i);
            if (j >> 8) return; // ASCII check: only accept characters in range 0-255
            words[i >> 2] |= j << ((3 - i) % 4) * 8;
        }
        words[words[lengthProperty]] = ((asciiBitLength / maxWord) | 0);
        words[words[lengthProperty]] = (asciiBitLength)

        // process each chunk
        for (j = 0; j < words[lengthProperty];) {
            var w = words.slice(j, j += 16); // The message is expanded into 64 words as part of the iteration
            var oldHash = hash;
            // This is now the undefinedworking hash, often labelled as variables a...g
            // (we have to truncate as well, otherwise extra entries at the end accumulate
            hash = hash.slice(0, 8);

            for (i = 0; i < 64; i++) {
                var i2 = i + j;
                // Expand the message into 64 words
                var w15 = w[i - 15], w2 = w[i - 2];

                // Iterate
                var a = hash[0], e = hash[4];
                var temp1 = hash[7]
                    + ( pimcorecastsHashCash.rightRotate(e, 6) ^ pimcorecastsHashCash.rightRotate(e, 11) ^ pimcorecastsHashCash.rightRotate(e, 25)) // S1
                    + ((e & hash[5]) ^ ((~e) & hash[6])) // ch
                    + k[i]
                    // Expand the message schedule if needed
                    + (w[i] = (i < 16) ? w[i] : (
                            w[i - 16]
                            + (pimcorecastsHashCash.rightRotate(w15, 7) ^ pimcorecastsHashCash.rightRotate(w15, 18) ^ (w15 >>> 3)) // s0
                            + w[i - 7]
                            + (pimcorecastsHashCash.rightRotate(w2, 17) ^ pimcorecastsHashCash.rightRotate(w2, 19) ^ (w2 >>> 10)) // s1
                        ) | 0
                    );
                // This is only used once, so *could* be moved below, but it only saves 4 bytes and makes things unreadble
                var temp2 = (pimcorecastsHashCash.rightRotate(a, 2) ^ pimcorecastsHashCash.rightRotate(a, 13) ^ pimcorecastsHashCash.rightRotate(a, 22)) // S0
                    + ((a & hash[1]) ^ (a & hash[2]) ^ (hash[1] & hash[2])); // maj

                hash = [(temp1 + temp2) | 0].concat(hash); // We don't bother trimming off the extra ones, they're harmless as long as we're truncating when we do the slice()
                hash[4] = (hash[4] + temp1) | 0;
            }

            for (i = 0; i < 8; i++) {
                hash[i] = (hash[i] + oldHash[i]) | 0;
            }
        }

        for (i = 0; i < 8; i++) {
            for (j = 3; j + 1; j--) {
                var b = (hash[i] >> (j * 8)) & 255;
                result += ((b < 16) ? 0 : '') + b.toString(16);
            }
        }
        return result;
    },

    // Only need for SHA256 Function
    rightRotate: function rightRotate(value, amount) {
        return (value >>> amount) | (value << (32 - amount));
    },

    // replace with your desired hash function
    hashString: function( toHash ){
        return pimcorecastsHashCash.sha256( toHash )
    },

    // set the data for a specific input
    setFormData: function(form, inputName, inputValue){
        var z = form.querySelectorAll("[name=" + inputName + "]")[0]
        if (z){
            z.value = inputValue;
        }
    },

    // get data from form input by name
    getFormData: function( form, inputName ){
        var z = form.querySelectorAll("[name=" + inputName + "]")[0]
        if (z)
            return z.value;
        else
            return '';
    },

    // convert hex char to binary string
    hexInBin: function( x ){
        var ret = ''
        switch (x.toUpperCase()) {
            case '0':
                return '0000';
            case '1':
                return '0001';
            case '2':
                return '0010';
            case '3':
                return '0011';
            case '4':
                return '0100';
            case '5':
                return '0101';
            case '6':
                return '0110';
            case '7':
                return '0111';
            case '8':
                return '1000';
            case '9':
                return '1001';
            case 'A':
                return '1010';
            case 'B':
                return '1011';
            case 'C':
                return '1100';
            case 'D':
                return '1101';
            case 'E':
                return '1110';
            case 'F':
                return '1111';
            default :
                return '0000';
        }
    },

    // gets the leading number of bits from the string
    extractBits: function ( hex_string, num_bits ){
        var bit_string = "";
        var num_chars = Math.ceil(num_bits / 4);
        for (var i = 0; i < num_chars; i++) {
            bit_string = bit_string + "" + pimcorecastsHashCash.hexInBin(hex_string.charAt(i));
        }
        // todo: check
        bit_string = bit_string.slice(0, num_bits);
        return bit_string;
    },

    // check if a given nonce is a solution for this stamp and difficulty
    // the $difficulty number of leading bits must all be 0 to have a valid solution
    checkNonce: function ( difficulty, stamp, nonce ){
        var col_hash = pimcorecastsHashCash.hashString(stamp + nonce);
        var check_bits = pimcorecastsHashCash.extractBits(col_hash, difficulty);
        return (check_bits == 0);
    },

    // sleep Function for Calculating field ... we dont want to burn every pc
    sleep: function (ms){
        return new Promise(resolve => setTimeout(resolve, ms));
    },

    // iterate through as many nonces as it takes to find one that gives us a solution hash at the target difficulty
    findHash: async function( form ){
        var pchc_stamp = pimcorecastsHashCash.getFormData(form, 'pchc_stamp');
        var pchc_difficulty = pimcorecastsHashCash.getFormData(form, 'pchc_difficulty');

        // check to see if we already found a solution
        var form_nonce = pimcorecastsHashCash.getFormData(form, 'pchc_nonce');
        if (form_nonce && pimcorecastsHashCash.checkNonce(pchc_difficulty, pchc_stamp, form_nonce)) {
            // we have a valid nonce; submit the form
            //document.getElementById('submitbutton').disabled = false;
            return true;
        }

        var nonce = 1;

        while (!pimcorecastsHashCash.checkNonce(pchc_difficulty, pchc_stamp, nonce)) {
            nonce++;
            if (nonce % 10000 == 0) {
                let remaining = Math.round((Math.pow(2, pchc_difficulty) - nonce) / 10000) * 10000;
                var text = "     Approximately " + remaining + " hashes remaining before form unlocks."
                console.log(text)
                //document.getElementById('countdown').innerHTML = text;

                await pimcorecastsHashCash.sleep(100); // don't peg the CPU and prevent the browser from rendering these updates
            }
        }

        pimcorecastsHashCash.setFormData(form, 'pchc_nonce', nonce);

        // we have a valid nonce; enable the form submit button
        // document.getElementById('countdown').innerHTML = "a";

        return true;
    }

}

/**
 * Start the HashCash Script for every Form where the Submit Button has the Class: .pchc-submit
 *
 * This searches the forms submit button and prevents the form submit until the test is done correct
 */
window.addEventListener('load', (event) => {
    var allAjaxForms = document.querySelectorAll('.pchc-form-ajax');
    allAjaxForms.forEach((formElement) => {
        fetch('/pchc/ajax/create-stamp').then(function(response) {
            return response.json();
        }).then(function(data) {
            for (const property in data) {
                console.log(`${property}: ${data[property]}`);
                console.log(formElement);
                var input = document.createElement("input");
                input.type = "hidden";
                input.name = property;
                input.value = data[property];
                formElement.appendChild(input);
            }
        }).catch(function (err) {
            console.log('Fetch Error :-S', err);
        })
    })
    var allForms = document.querySelectorAll('.pchc-form');
    allForms.forEach((formElement) => {

            formElement.addEventListener('submit', (event) => {
                event.preventDefault()
                var submittedForm = event.target

                var submitButtons = submittedForm.querySelectorAll('button[type="submit"], button:not([type])')
                submitButtons.forEach( (el) => {
                    el.setAttribute('disabled', true)
                } )

                pimcorecastsHashCash.findHash(submittedForm).then( ( isValid ) => {
                    var newAction = submittedForm.getAttribute('data-action')
                    var hasActionAttr = submittedForm.hasAttribute('data-action')

                    if (hasActionAttr) {
                        submittedForm.action = newAction;
                    }

                    var customEvent = new CustomEvent('hashcashFormValid', {
                        bubbles: true,
                        detail: {
                            valid: isValid,
                            submitForm: true
                        }
                    })
                    submittedForm.dispatchEvent(customEvent)

                    if (customEvent.detail.submitForm && customEvent.detail.valid) {
                        submittedForm.submit();
                    }
                } )

            })

        }
    )

    /* example
    document.addEventListener('hashcashFormValid', (event) => {
        event.detail.submitForm = false
        console.log(event)
    })
    */
})