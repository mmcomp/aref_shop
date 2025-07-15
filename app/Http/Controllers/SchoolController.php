<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSchoolRequest;
use App\Http\Requests\UpdateSchoolRequest;
use App\Http\Resources\SchoolCollection;
use App\Http\Resources\SchoolResource;
use App\Models\School;
use Illuminate\Http\Request;
class SchoolController extends Controller
{
    public function index(Request $request)
    {
        $sort = "id";
        $sort_dir = "desc";
        if ($request->get('sort_dir') != null && $request->get('sort') != null) {
            $sort = $request->get('sort');
            $sort_dir = $request->get('sort_dir');
        }
        $schools = School::with('city')->orderBy($sort, $sort_dir);
        if ($request->get('name') != null) {
            $schools->where('name', 'like', '%' . $request->get('name') . '%');
        }

        if ($request->get('per_page') == "all") {
            $paginated_schools = $schools->get();
        } else {
            $paginated_schools = $schools->paginate(env('PAGE_COUNT'));
        }
        return new SchoolCollection($paginated_schools);
    }

    public function show(School $school)
    {
        $school->load('city.province');
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
