<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSchoolRequest;
use App\Http\Requests\UpdateSchoolRequest;
use App\Http\Resources\SchoolCollection;
use App\Http\Resources\SchoolResource;
use App\Models\School;

class SchoolController extends Controller
{
    public function index()
    {
        $schools = School::with('city')->get();
        return new SchoolCollection($schools);
    }

    public function show(School $school)
    {
        return new SchoolResource($school);
    }

    public function store(CreateSchoolRequest $request)
    {
        $school = School::create($request->all());
        return new SchoolResource($school);
    }

    public function update(UpdateSchoolRequest $request, School $school)
    {
        $school->update($request->all());
        return new SchoolResource($school);
    }

    public function destroy(School $school)
    {
        $users = $school->users;
        if ($users->count() > 0) {
            return (new SchoolResource(null))->additional([
                'errors' => ['school' => ['School has users']],
            ])->response()->setStatusCode(400);
        }
        $school->delete();
        return new SchoolResource($school);
    }
}
