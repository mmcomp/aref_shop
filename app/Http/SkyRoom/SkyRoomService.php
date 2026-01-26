<?php

namespace App\Http\SkyRoom;

use Illuminate\Support\Facades\Http;
use App\Models\SkyRoom as SkyRoomModel;

class SkyRoom
{
    public $id;
    public $name;
    public $title;
    public $status;
    public $guest_login;
    public $op_login_first;
    public $max_users;
    public $service_id;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? null;
        $this->title = $data['title'] ?? null;
        $this->status = $data['status'] ?? null;
        $this->service_id = $data['service_id'] ?? null;
        $this->guest_login = $data['guest_login'] ?? null;
        $this->op_login_first = $data['op_login_first'] ?? null;
        $this->max_users = $data['max_users'] ?? null;
    }

    public function toArray()
    {
        return [
            "name" => $this->name,
            "title" => $this->title,
            "status" => $this->status,
            "guest_login" => $this->guest_login,
            "op_login_first" => $this->op_login_first,
            "max_users" => $this->max_users,
            "service_id" => $this->service_id,
        ];
    }
}

class SkyRoomCollection
{
    public $rooms;
    public function __construct(array $roomDatas)
    {
        $this->rooms = [];
        foreach($roomDatas as $room){
            $this->rooms[] = new SkyRoom($room);
        }
    }
}

class SkyRoomUser
{
    public $id;
    public $username;
    public $password;
    public $nickname;
    public $status;
    public $is_public;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? null;
        $this->username = $data['username'] ?? null;
        $this->password = $data['password'] ?? null;
        $this->nickname = $data['nickname'] ?? null;
        $this->status = $data['status'] ?? null;
        $this->is_public = $data['is_public'] ?? null;
    }

    public function toArray()
    {
        return [
            "username" => $this->username,
            "password" => $this->password,
            "nickname" => $this->nickname,
            "status" => $this->status,
            "is_public" => $this->is_public,
        ];
    }
}

class SkyRoomCommonResponse
{
    public $result;
    public $ok;

    public function __construct(array $data)
    {
        $this->result = $data['result'] ?? null;
        $this->ok = $data['ok'];
    }
}

class SkyRoomService
{
    private $apiKey;
    private $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('SKYROOM_API_KEY');
        $this->baseUrl = env('SKYROOM_BASE_URL');
    }

    public function getRooms(): SkyRoomCollection
    {
        $response = Http::post($this->baseUrl . $this->apiKey, ["action" => "getRooms"]);
        $responseData = $response->json();
        $resp = new SkyRoomCommonResponse($responseData);
        if(!$resp->ok){
            throw new \Exception($responseData["error_message"]);
        }
        $roomData = $resp->result;
        return new SkyRoomCollection($roomData);
    }

    public function getRoom(int $id): SkyRoom
    {
        $response = Http::post($this->baseUrl . $this->apiKey, ["action" => "getRoom", "params" => ["room_id" => $id]]);
        $responseData = $response->json();
        $resp = new SkyRoomCommonResponse($responseData);
        if(!$resp->ok){
            throw new \Exception($responseData["error_message"]);
        }
        return new SkyRoom($resp->result);
    }

    private function _createRoom(SkyRoom $room): int
    {
        $response = Http::post($this->baseUrl . $this->apiKey, ["action" => "createRoom", "params" => $room->toArray()]);
        $responseData = $response->json();
        $resp = new SkyRoomCommonResponse($responseData);
        if(!$resp->ok){
            throw new \Exception($responseData["error_message"]);
        }
        return $resp->result;
    }

    public function deleteRoom(int $id): bool
    {
        $response = Http::post($this->baseUrl . $this->apiKey, ["action" => "deleteRoom", "params" => ["room_id" => $id]]);
        $responseData = $response->json();
        $resp = new SkyRoomCommonResponse($responseData);
        if(!$resp->ok){
            throw new \Exception($responseData["error_message"]);
        }
        return $resp->ok;
    }

    public function createRoom(SkyRoom $room): SkyRoomModel
    {
        $roomId = $this->_createRoom($room);
        $room = $this->getRoom($roomId);
        $model = new SkyRoomModel([
            "id" => $room->id,
            "name" => $room->name,
            "title" => $room->title,
            "status" => $room->status,
            "service_id" => $room->service_id,
            "guest_login" => $room->guest_login,
            "op_login_first" => $room->op_login_first,
            "max_users" => $room->max_users,
        ]);
        $model->save();
        return $model;
    }

    public function createUser(SkyRoomUser $user): int
    {
        $response = Http::post($this->baseUrl . $this->apiKey, ["action" => "createUser", "params" => $user->toArray()]);
        $responseData = $response->json();
        $resp = new SkyRoomCommonResponse($responseData);
        if(!$resp->ok){
            throw new \Exception($responseData["error_message"]);
        }
        return $resp->result;
    }
}
