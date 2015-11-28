<?php
/**
 * EventPostJob.php
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author    jack <linjue@wilead.com>
 * @copyright 2007-15/11/28 WIZ TECHNOLOGY
 * @link      http://wizmacau.com
 * @link      http://jacklam.it
 * @link      https://github.com/lamjack
 * @version
 */
namespace Acme\AppBundle\Jobs;

use Network\Curl;
use Wiz\ResqueBundle\Job\ContainerAwareJob;

/**
 * Class EventPostJob
 * @package Acme\AppBundle\Jobs
 */
class EventPostJob extends ContainerAwareJob
{
    /**
     * @param $args
     */
    public function run($args)
    {
        $json = $args['json'];

        $data = json_decode($json, true);

        try {
            $curl = new Curl();
            $curl->post('http://192.168.10.94:5000/incident', $data, array(
                'json' => true
            ));
        } catch (\Exception $e) {

        }
    }
}