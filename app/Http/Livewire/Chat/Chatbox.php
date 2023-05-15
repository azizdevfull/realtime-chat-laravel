<?php

namespace App\Http\Livewire\Chat;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Livewire\Component;

class Chatbox extends Component
{
    public $selectedConversation;
    public $receiverInstance;
    public $messages;
    public $message_count;
    public $paginateVar = 10;
    protected $listeners = ['loadConversation'];

    public function loadConversation(Conversation $conversation, User $receiver)
    {
        // dd($conversation,$receiver);
       $this->selectedConversation = $conversation;
       $this->receiverInstance = $receiver;

       $this->message_count = Message::where('conversation_id',$this->selectedConversation->id)->count();
       $this->messages = Message::where('conversation_id',$this->selectedConversation->id)->skip($this->message_count - $this->paginateVar)->take($this->paginateVar)->get();

       $this->dispatchBrowserEvent('chatSelected');
    }

    public function render()
    {
        return view('livewire.chat.chatbox');
    }
}
