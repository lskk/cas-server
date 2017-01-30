<?php


namespace App\Http\Controllers\Cas;


use App\Http\Controllers\Controller;
use App\Services\Ticket;
use App\Exceptions\CAS\CasException;
use App\Models\Ticket as TicketModel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ValidateController extends Controller
{
    const BASE_XML = '<cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas"></cas:serviceResponse>';

    public function v1ValidateAction(Request $request)
    {
        $service = $request->get('service', '');
        $ticket  = $request->get('ticket', '');
        if (empty($service) || empty($ticket)) {
            return new Response('no');
        }

        if (!$this->lockTicket($ticket)) {
            return new Response('no');
        }
        $record = Ticket::getByTicket($ticket);
        if (!$record || $record->service_url != $service) {
            $this->unlockTicket($ticket);

            return new Response('no');
        }
        Ticket::invalidTicket($record);

        $this->unlockTicket($ticket);

        return new Response('yes');
    }

    public function v2ValidateAction(Request $request)
    {
        return $this->casValidate($request, false);
    }

    public function v3ValidateAction(Request $request)
    {
        return $this->casValidate($request, true);
    }

    /**
     * @param Request $request
     * @param bool    $returnAttr
     * @return Response
     */
    protected function casValidate(Request $request, $returnAttr)
    {
        $service = $request->get('service', '');
        $ticket  = $request->get('ticket', '');
        $format  = strtoupper($request->get('format', 'XML'));
        if (empty($service) || empty($ticket)) {
            return $this->failureResponse(
                CasException::INVALID_REQUEST,
                'param service and ticket can not be empty',
                $format
            );
        }

        if (!$this->lockTicket($ticket)) {
            return $this->failureResponse(CasException::INTERNAL_ERROR, 'try to lock ticket failed', $format);
        }

        $record = Ticket::getByTicket($ticket);
        try {
            if (!$record) {
                throw new CasException(CasException::INVALID_TICKET, 'ticket is not valid');
            }

            if ($record->service_url != $service) {
                throw new CasException(CasException::INVALID_SERVICE, 'service is not valid');
            }
        } catch (CasException $e) {
            //invalid ticket if error occur
            $record instanceof TicketModel && Ticket::invalidTicket($record);
            $this->unlockTicket($ticket);

            return $this->failureResponse($e->getCasErrorCode(), $e->getMessage(), $format);
        }
        Ticket::invalidTicket($record);
        $this->unlockTicket($ticket);

        $attr = $returnAttr ? [
            'email'    => $record->user->email,
            'realName' => $record->user->real_name,
        ] : [];

        return $this->successResponse($record->user->name, $attr, $format);
    }

    /**
     * @param $username
     * @param $attrs
     * @param $format
     * @return Response
     */
    protected function successResponse($username, $attrs, $format)
    {
        if (strtoupper($format) === 'JSON') {
            $data = [
                'serviceResponse' => [
                    'authenticationSuccess' => [
                        'user' => $username,
                    ],
                ],
            ];

            if (!empty($attrs)) {
                $data['serviceResponse']['authenticationSuccess']['attributes'] = $attrs;
            }

            return new Response($data);
        } else {
            $xml          = simplexml_load_string(self::BASE_XML);
            $childSuccess = $xml->addChild('cas:authenticationSuccess');
            $childSuccess->addChild('cas:user', $username);

            if (!empty($attrs)) {
                $childAttrs = $childSuccess->addChild('cas:attributes');
                foreach ($attrs as $key => $value) {
                    $childAttrs->addChild('cas:'.$key, $value);
                }
            }

            return $this->returnXML($xml);
        }
    }

    /**
     * @param $code
     * @param $desc
     * @param $format
     * @return Response
     */
    protected function failureResponse($code, $desc, $format)
    {
        if (strtoupper($format) === 'JSON') {
            return new Response(
                [
                    'serviceResponse' => [
                        'authenticationFailure' => [
                            'code'        => $code,
                            'description' => $desc,
                        ],
                    ],
                ]
            );
        } else {
            $xml          = simplexml_load_string(self::BASE_XML);
            $childFailure = $xml->addChild('cas:authenticationFailure', $desc);
            $childFailure->addAttribute('code', $code);

            return $this->returnXML($xml);
        }
    }

    /**
     * @param string $ticket
     * @return bool
     */
    protected function lockTicket($ticket)
    {
        return \App::make('locker')->acquireLock($ticket, config('cas.lock_timeout'));
    }

    /**
     * @param string $ticket
     * @return bool
     */
    protected function unlockTicket($ticket)
    {
        return \App::make('locker')->releaseLock($ticket);
    }

    /**
     * remove the first line of xml string
     * @param string $str
     * @return string
     */
    protected function removeXmlFirstLine($str)
    {
        $first = '<?xml version="1.0"?>';
        if (stripos($str, $first) === 0) {
            return trim(substr($str, strlen($first)));
        }

        return $str;
    }

    /**
     * @param \SimpleXMLElement $xml
     * @return Response
     */
    protected function returnXML(\SimpleXMLElement $xml)
    {
        return new Response($this->removeXmlFirstLine($xml->asXML()), 200, array('Content-Type' => 'application/xml'));
    }
}