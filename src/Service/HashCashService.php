<?php
/**
 *
 * Date: 06.09.2022
 * Time: 13:02
 *
 */
namespace Pimcorecasts\Bundle\HashCash\Service;

use Pimcore\Model\Tool\TmpStore;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class HashCashService
{

    protected Request $currentRequest;
    /*
     * number of bits to collide
     * Approximate number of hash guesses needed for difficulty target of:
     * 1-4: 10
     * 5-8: 100
     * 9-12: 1.000
     * 13-16: 10.000
     * 17-20: 100.000
     * 21-24: 1.000.000
     * 25-28: 10.000.000
     * 29-32: 100.000.000
     */
    protected int $hashcashDifficulty = 10;

    // time flexibility, in minutes, between stamp generation and expiration
    // allows time for clock drift and for filling out form
    // Note that higher values require higher resources to validate
    // that a given puzzle has not expired
    protected int $hashcashTimeWindow = 10;

    // Prefix only for DB: tmp_store
    protected string $tmpStorePrefix = 'pc-hc-';

    /**
     * @param int $hashcashDifficulty
     */
    public function setHashcashDifficulty(int $hashcashDifficulty): void
    {
        $this->hashcashDifficulty = $hashcashDifficulty;
    }

    /**
     * @param int $hashcashTimeWindow
     */
    public function setHashcashTimeWindow(int $hashcashTimeWindow): void
    {
        $this->hashcashTimeWindow = $hashcashTimeWindow;
    }


    /**
     * @param ContainerBagInterface $params
     * @param RequestStack $requestStack
     * @param string $hashcashSalt
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct( 
        private ContainerBagInterface $params, 
        private RequestStack $requestStack,
        protected string $hashcashSalt = '' 
    ){
        if( $hashcashSalt == '' ){
            $this->hashcashSalt = $params->get( 'secret' );
        }

        try {
            $this->currentRequest = $this->requestStack->getCurrentRequest();
        } catch (\Throwable $e) {
            // do nothing
        }
    }

    protected $flashBag = null;
    protected function getFlashBag() {
        if ($this->flashBag === null) {
            $this->flashBag = $this->requestStack->getSession()->getFlashBag();
        }
        return $this->flashBag;
    }

    /**
     * todo: Check and update function
     *
     * @return bool
     */
    public function validateHashcashCaptcha() : bool
    {
        $formError = '';

        if( !$this->checkStamp() ){
            $formError = 'pchc.invalid.try-again';
            $this->addFlashbagMessage( 'pchc_error', $formError);
        }

        if( $formError != '' ){
            return false;
        }

        // log that this puzzle has been used
        $postStamp = $this->getRequestParam('pchc_stamp' );
        TmpStore::add( $this->tmpStorePrefix . $postStamp, $postStamp, 'pchc', 3600);

        // Form submission validated; send message
        return true;
    }

    /**
     * Request Params
     *
     * @param string $name
     * @param [type] $defaultValue
     * @return void
     */
    private function getRequestParam(string $name, $defaultValue = null) : mixed {
        if (isset($this->currentRequest) && $this->currentRequest != '') {
            return $this->currentRequest->get($name, $defaultValue);
        }
        return $defaultValue;
    }

    /**
     * Add Flashbag Messages
     *
     * @param string $type
     * @param string $message
     * @return void
     */
    private function addFlashbagMessage(string $type, string $message): void {
        if ($this->getFlashBag() != '') {
            $this->getFlashBag()->add($type, $message);
        }
    }

    /**
     * Attempt to determine the client's IP address
     *
     * @return string
     */
    private function getClientIp() : string
    {
        $clientIp = $this->requestStack?->getCurrentRequest()?->getClientIp();
        if( $clientIp ){
            return $clientIp;
        }
        return 'UNKNOWN';
    }

    /**
     * drop in your desired hash function here
     *
     * @param $hashString
     * @return string
     */
    private function hashString( $hashString ) : string
    {
        return hash( 'sha256', $hashString );
    }

    /**
     * Get the first num_bits of data from this string
     *
     * @param $hex_string
     * @param $num_bits
     * @return string
     */
    private function extractBits( $hex_string, $num_bits ) : string
    {
        $bit_string = "";
        $num_chars = ceil( $num_bits / 4 );
        for( $i = 0; $i < $num_chars; $i++ )
            $bit_string .= str_pad( base_convert( $hex_string[ $i ], 16, 2 ), 4, "0", STR_PAD_LEFT ); // convert hex to binary and left pad with 0s

        // p_r( "requested $num_bits bits from $hex_string, returned $bit_string as " . substr( $bit_string, 0, $num_bits ) );
        return substr( $bit_string, 0, $num_bits );
    }

    /**
     * generate a stamp
     *
     * @return array
     */
    public function createStamp() : array
    {
        $ip = $this->getClientIp();
        $now = intval( time() / 60 );

        // stamp = hash of time (in minutes) . user ip . salt value
        $stamp = $this->hashString( $now . $ip . $this->hashcashSalt );

        return [
            'pchc_stamp' => $stamp,
            'pchc_difficulty' => $this->hashcashDifficulty,
            'pchc_nonce' => ( $this->getRequestParam('pchc_nonce', false) && $this->checkStamp() ) ? $this->getRequestParam('pchc_nonce') : ''
        ];
    }

    /**
     * check that the stamp is within our allowed time window
     * this function also implicitly validates that the IP address and salt match
     *
     * @param $stamp
     * @return bool
     */
    private function checkExpiration( $stamp ) : bool
    {
        $tempnow = intval( time() / 60 );
        $ip = $this->getClientIp();

        // gen hashes for $tempnow - $tolerance to $tempnow + $tolerance
        for( $i = -1 * $this->hashcashTimeWindow; $i < $this->hashcashTimeWindow; $i++ ){
            //p_r( "checking $stamp versus " . $this->hashString( ( $tempnow - $i ) . $ip . $this->hashcashSalt ) );
            if( $stamp === $this->hashString( ( $tempnow + $i ) . $ip . $this->hashcashSalt ) ){
                //p_r( "stamp matched at " . $i . " minutes from now" );
                return true;
            }
        }

        //p_r( "stamp expired" );
        $this->addFlashbagMessage( 'pchc_error', 'pchc.stamp.expired');
        return false;
    }

    /**
     * check that the hash of the stamp + nonce meets the difficulty target
     *
     * @param $difficulty
     * @param $stamp
     * @param $nonce
     * @return bool
     */
    private function checkProofOfWork( $difficulty, $stamp, $nonce ) : bool
    {
        // get hash of $stamp & $nonce
        //p_r( "checking $difficulty bits of work" );
        $work = $this->hashString( $stamp . $nonce );

        $leadingBits = $this->extractBits( $work, $difficulty );

        //p_r( "checking $leadingBits leading bits of $work for difficulty $difficulty match" );

        // if the leading bits are all 0, the difficulty target was met
        return ( strlen( $leadingBits ) > 0 && intval( $leadingBits ) == 0 );
    }

    /**
     * checks validity, expiration, and difficulty target for a stamp
     *
     * @return bool
     */
    private function checkStamp() : bool
    {
        $stamp = $this->getRequestParam( 'pchc_stamp');
        $client_difficulty = $this->getRequestParam('pchc_difficulty');
        $nonce = $this->getRequestParam( 'pchc_nonce' );

        if( $client_difficulty != $this->hashcashDifficulty ){
            $this->dispatcher->dispatch(new HashCashInvalidEvent('pchc.different-difficulty'));
            return false;
        };

        $expectedLength = strlen( $this->hashString( hashString: uniqid() ) );
        if( strlen( $stamp ) != $expectedLength ){
            $this->dispatcher->dispatch(new HashCashInvalidEvent('pchc.stamp-length'));
            return false;
        }

        if( !$this->checkExpiration( stamp: $stamp ) ){
            // PoW puzzle expired
            $this->dispatcher->dispatch(new HashCashInvalidEvent('pchc.puzzle-expired'));
            $this->addFlashbagMessage( 'pchc_error', 'pchc.puzzle-expired');
            return false;
        }

        // check the actual PoW
        if( !$this->checkProofOfWork( difficulty: $this->hashcashDifficulty, stamp: $stamp, nonce: $nonce ) ){
            // Difficulty target was not met.
            $this->dispatcher->dispatch(new HashCashInvalidEvent('pchc.generic-error'));
            $this->addFlashbagMessage( 'pchc_error', 'pchc.generic-error');
            return false;
        }

        // check if this puzzle has already been used to submit a message
        if( TmpStore::get( $this->tmpStorePrefix . $stamp) ){
            $this->dispatcher->dispatch(new HashCashInvalidEvent('pchc.puzzle-is-already-used'));
            $this->addFlashbagMessage( 'pchc_error', "pchc.puzzle-is-already-used");
            return false;
        }

        return true;
    }

}
