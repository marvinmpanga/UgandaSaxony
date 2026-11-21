<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendContactMessageRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Forward a public contact-form submission to the partnership inbox.
     */
    public function store(SendContactMessageRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $body = implode("\n", [
            "Name: {$data['from_name']}",
            "Email: {$data['from_email']}",
            'Phone: '.($data['from_mobile'] ?? '-'),
            'Location: '.($data['from_address'] ?? '-'),
            'Organisation: '.($data['from_organisation'] ?? '-'),
            '',
            $data['message'],
        ]);

        Mail::raw($body, function (Message $message) use ($data): void {
            $message->to(config('partnership.contact_recipient'))
                ->replyTo($data['from_email'], $data['from_name'])
                ->subject('Website enquiry from '.$data['from_name']);
        });

        return back()->with('success', 'Thank you — your message has been sent.');
    }
}
