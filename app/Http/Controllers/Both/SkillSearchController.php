<?php

namespace App\Http\Controllers\Both;

use App\Http\Controllers\Controller;

use App\Models\Skill;
use Illuminate\Http\Request;

class SkillSearchController extends Controller
{
    public function skillSearch() {
        $skills = Skill::all("skill_id", "skill");
        return response()->json($skills);
    }
}
