<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Users;
class UsersController extends BaseController
{
    public function index()
    {
        return view('index');
    }

    public function getUsers(){

        $request = service('request');
        $postData = $request->getPost();
        $dtpostData = $postData['data'];
        $response = array();

        ## Read value
        $draw = $dtpostData['draw'];
        $start = $dtpostData['start'];
        $rowperpage = $dtpostData['length']; // Rows display per page
        $columnIndex = $dtpostData['order'][0]['column']; // Column index
        $columnName = $dtpostData['columns'][$columnIndex]['data']; // Column name
        $columnSortOrder = $dtpostData['order'][0]['dir']; // asc or desc
        $searchValue = $dtpostData['search']['value']; // Search value

        ## Total number of records without filtering
        $users = new Users();
        $totalRecords = $users->select('id')
                     ->countAllResults();

        ## Total number of records with filtering
        $totalRecordwithFilter = $users->select('id')
            ->orLike('name', $searchValue)
            ->orLike('email', $searchValue)
            ->orLike('city', $searchValue)
            ->countAllResults();

        ## Fetch records
        $records = $users->select('*')
            ->orLike('name', $searchValue)
            ->orLike('email', $searchValue)
            ->orLike('city', $searchValue)
            ->orderBy($columnName,$columnSortOrder)
            ->findAll($rowperpage, $start);

        $data = array();

        foreach($records as $record ){

            $data[] = array( 
                "id"=>$record['id'],
                "name"=>$record['name'],
                "email"=>$record['email'],
                "city"=>$record['city']
            ); 
        }

        ## Response
        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordwithFilter,
            "aaData" => $data,
            "token" => csrf_hash() // New token hash
        );

        return $this->response->setJSON($response);
    }
}
