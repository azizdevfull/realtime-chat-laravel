<?php

namespace App\Http\Livewire\Chat;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Livewire\Component;
use App\Events\MessageSent;
class Chatbox extends Component
{
    public $selectedConversation;
    public $receiverInstance;
    public $messages;
    public $height;
    public $message_count;
    public $messages_count;
    public $paginateVar = 10;
    // protected $listeners = [ 'loadConversation', 'pushMessage', 'loadmore', 'updateHeight', "echo-private:chat. {$auth_id},MessageSent"=>'broadcastedMessageReceived',];

    public function  getListeners()
    {

        $auth_id = auth()->user()->id;
        return [
            "echo-private:chat.{$auth_id},MessageSent" => 'broadcastedMessageReceived',
            // "echo-private:chat.{$auth_id},MessageRead" => 'broadcastedMessageRead',
            'loadConversation', 'pushMessage', 'loadmore', 'updateHeight','broadcastMessageRead','resetComponent'
        ];
    }

    public function broadcastedMessageRead($event)
    {
        //dd($event);

        if($this->selectedConversation){



            if((int) $this->selectedConversation->id === (int) $event['conversation_id']){

                $this->dispatchBrowserEvent('markMessageAsRead');
            }

        }

        # code...
    }

    function broadcastedMessageReceived($event)
    {
        ///here
      $this->emitTo('chat.chat-list','refresh');
        # code...

        $broadcastedMessage = Message::find($event['message']);


        #check if any selected conversation is set
        if ($this->selectedConversation) {
            #check if Auth/current selected conversation is same as broadcasted selecetedConversationgfg
            if ((int) $this->selectedConversation->id  === (int)$event['conversation_id']) {
                # if true  mark message as read
                $broadcastedMessage->read = 1;
                $broadcastedMessage->save();
                $this->pushMessage($broadcastedMessage->id);
                // dd($event);

                $this->emitSelf('broadcastMessageRead');
            }
        }
    }


    public function pushMessage($messageId)
    {
        $newMessage = Message::find($messageId);
        $this->messages->push($newMessage);
        $this->dispatchBrowserEvent('rowChatToBottom');
        # code...
    }



    public function loadConversation(Conversation $conversation, User $receiver)
    {
        // dd($conversation,$receiver);
       $this->selectedConversation = $conversation;
       $this->receiverInstance = $receiver;

       $this->message_count = Message::where('conversation_id',$this->selectedConversation->id)->count();
       $this->messages = Message::where('conversation_id',$this->selectedConversation->id)->skip($this->message_count - $this->paginateVar)->take($this->paginateVar)->get();

       $this->dispatchBrowserEvent('chatSelected');
    }

    function loadmore()
    {

        // dd('top reached ');
        $this->paginateVar = $this->paginateVar + 10;
        $this->messages_count = Message::where('conversation_id', $this->selectedConversation->id)->count();

        $this->messages = Message::where('conversation_id',  $this->selectedConversation->id)
            ->skip($this->messages_count -  $this->paginateVar)
            ->take($this->paginateVar)->get();

        $height = $this->height;
        $this->dispatchBrowserEvent('updatedHeight', ($height));
        # code...
    }

    function updateHeight($height)
    {

        // dd($height);
        $this->height = $height;

        # code...
    }


    public function render()
    {
        return view('livewire.chat.chatbox');
    }
}
