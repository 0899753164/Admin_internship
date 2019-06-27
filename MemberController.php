<?php
 

namespace App\Http\Controllers;

use App\Member;
use App\department;
use DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Input;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use App\Work;
use App\quota;

use App\Http\Controllers\http\Client;


class MemberController extends Controller
{
    public function __construct()
    {
      $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {

         
        $departments = department::all();
        $members = Member::leftjoin('departmentm','member.did','=','departmentm.did')
                    ->orderBy('member.mstatus');
        $cns = $members->get();
        $countmember = $cns->count();
        $search=$request->Input('search');
        if (!empty($search)) {
            $members->Where('mname','LIKE','%'.$search.'%');
        }
        $work1  = Work::join('member','work.mid','=','member.mid')->join('detailwork','work.wid','=','detailwork.wid')->where('status','=',"")->get();

        $xs = count($work1); 

        $members=$members->paginate(10);
        return view('admin.user_page',['members'=>$members,'departments'=>$departments,'count'=>$countmember,'xs'=>$xs]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $departments = department::all();

        return view('admin.add_user',compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show(Request $request)
    {
      //$date = explode('/', $request);
      //$date = $date[1].'/'.$date[0].'/'.$date[2];       
      if($request->hasFile('mpic')) 
      {
        /*select table where request id
          get name by id
        */
        $users = DB::table('departmentm')->where('did', $request->did)->get();
        foreach($users as $user)
        {
          if ($user->did == $request->did) 
          {
            $a = $user->dname;
          }
          else 
          {
            echo 'no';
          }
        }
        // temp b
        $b = $a;
        // get string value
        $qry_str = "?email=$request->memail&pass=$request->mpassword&firstname=$request->mname&lastname=$request->mlastname&position=$b&phone=$request->mtel";

        // cURL using cloudfunctions firebase API
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://us-central1-nilecon-chat-bot.cloudfunctions.net/adduser" .$qry_str,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
          "cache-control: no-cache",
          "postman-token: 8b53e0cf-fefe-6ac8-a1d1-29772576a26c"
        ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) 
        {
          echo "cURL Error #:" . $err;
        } 
        else
        {
          //decode $response value
          $outputArray = json_decode($response, TRUE);
          var_dump($outputArray);
          echo $outputArray["data"]; 
        }
        // temp $result
        $resultID = $outputArray["data"];
        echo 'result:'. $resultID;

        // insert in to Mysql database
        $file = Input::file('mpic');
        //getting timestamp
        $timestamp = str_replace([' ', ':'], '-', Carbon::now()->toDateTimeString());
        
        $name = $file->getClientOriginalName();
        $members = new Member;
        $members->muser=$request->muser;
        $members->mpassword=$request->mpassword;
        $members->mname=$request->mname;
        $members->mlastname=$request->mlastname;
        $members->mage=$request->mage;
        $members->msex=$request->msex;
        $members->mbirthday=$request->mbirthday;
        $members->maddress=$request->maddress;
        $members->midcard=$request->midcard;
        $members->mtel=$request->mtel;
        $members->mstart=$request->mstart;
        $members->memail=$request->memail;
        // $members->mreferance="";
        $members->mtype=$request->mtype;
        $members->did=$request->did;
        $members->hash="";
        $members->mpic = $name;
        $members->uid_rtdb = $resultID;
        $members->save();

   
          $quota = new quota();
          $quota->mid = $members->mid;
          $quota->type = 1;
          $quota->daytotal = 30;
          $quota->day = 30;
          $quota->year = date("Y");
          $quota->save();

          $quota = new quota();
          $quota->mid = $members->mid;
          $quota->type = 2;
          $quota->daytotal = 5;
          $quota->day = 5;
          $quota->year = date("Y");
          $quota->save();

          $quota = new quota();
          $quota->mid = $members->mid;
          $quota->type = 3;
          $quota->daytotal = 7;
          $quota->day = 7;
          $quota->year = date("Y");
          $quota->save();

          $quota = new quota();
          $quota->mid = $members->mid;
          $quota->type = 4;
          $quota->daytotal = 7;
          $quota->day = 7;
          $quota->year = date("Y");
          $quota->save();

          //return $members;
          $file->move(public_path().'/images/', $name);
      }
      else
      {
        if ($request->msex == 'male') 
        {
          /*select table where request id
            get name by id
          */
          $users = DB::table('departmentm')->where('did', $request->did)->get();
          foreach($users as $user)
          {
            if ($user->did == $request->did) 
            {
              $a = $user->dname;
            }
            else 
            {
              echo 'no';
            }
          }

          // temp b
          $b = $a;
          
          // get string value
          $qry_str = "?email=$request->memail&pass=$request->mpassword&firstname=$request->mname&lastname=$request->mlastname&position=$b&phone=$request->mtel";

          // cURL using cloudfunctions firebase API
          $curl = curl_init();

          curl_setopt_array($curl, array(
            CURLOPT_URL => "https://us-central1-nilecon-chat-bot.cloudfunctions.net/adduser" .$qry_str,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
              "cache-control: no-cache",
              "postman-token: 8b53e0cf-fefe-6ac8-a1d1-29772576a26c"
            ),
          ));

          $response = curl_exec($curl);
          $err = curl_error($curl);

          curl_close($curl);

          if ($err) 
          {
            echo "cURL Error #:" . $err;
          } 
          else 
          {
            //decode $response value
            $outputArray = json_decode($response, TRUE);
            var_dump($outputArray);
    
            echo $outputArray["data"]; 
    
          }
            // temp $result
            $resultID = $outputArray["data"];

            echo 'result:'. $resultID;
            //////////////////////////// END//////////////////////

                $members = new Member;
                $members->muser=$request->muser;
                $members->mpassword=$request->mpassword;
                $members->mname=$request->mname;
                $members->mlastname=$request->mlastname;
                $members->mage=$request->mage;
                $members->msex=$request->msex;
                $members->mbirthday=$request->mbirthday;
                $members->maddress=$request->maddress;
                $members->midcard=$request->midcard;
                $members->mtel=$request->mtel;
                $members->mstart=$request->mstart;
                $members->memail=$request->memail;
                // $members->mreferance="";
                $members->mtype=$request->mtype;
                $members->did=$request->did;
                $members->hash="";
                $members->mpic='journalist.png';
                $members->uid_rtdb = $resultID;
                $members->save();
           
                $quota = new quota();
                  $quota->mid = $members->mid;
                  $quota->type = 1;
                  $quota->daytotal = 30;
                  $quota->day = 30;
                  $quota->year = date("Y");
                  $quota->save();

                $quota = new quota();
                  $quota->mid = $members->mid;
                  $quota->type = 2;
                  $quota->daytotal = 5;
                  $quota->day = 5;
                  $quota->year = date("Y");
                  $quota->save();

                $quota = new quota();
                  $quota->mid = $members->mid;
                  $quota->type = 3;
                  $quota->daytotal = 7;
                  $quota->day = 7;
                  $quota->year = date("Y");
                  $quota->save();

                $quota = new quota();
                  $quota->mid = $members->mid;
                  $quota->type = 4;
                  $quota->daytotal = 7;
                  $quota->day = 7;
                  $quota->year = date("Y");
                  $quota->save();

                 //journalist.png ผู้ชาย
            
                    //return $members;
            }
            else
            {
              /*select table where request id
                get name by id
              */
              $users = DB::table('departmentm')->where('did', $request->did)->get();
              foreach($users as $user)
              {
                if ($user->did == $request->did) 
                {
                  $a = $user->dname;
                }
                else 
                {
                  echo 'no';
                }
            }

              // temp b
              $b = $a;
              // get string value
            $qry_str = "?email=$request->memail&pass=$request->mpassword&firstname=$request->mname&lastname=$request->mlastname&position=$b&phone=$request->mtel";

          // cURL using cloudfunctions firebase API
          $curl = curl_init();

          curl_setopt_array($curl, array(
            CURLOPT_URL => "https://us-central1-nilecon-chat-bot.cloudfunctions.net/adduser" .$qry_str,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
              "cache-control: no-cache",
              "postman-token: 8b53e0cf-fefe-6ac8-a1d1-29772576a26c"
            ),
          ));

          $response = curl_exec($curl);
          $err = curl_error($curl);

          curl_close($curl);

          if ($err) 
          {
            echo "cURL Error #:" . $err;
          } 
          else 
          {
             //decode $response value
            $outputArray = json_decode($response, TRUE);
            var_dump($outputArray);
            echo $outputArray["data"]; 
          }
            // temp $result
            $resultID = $outputArray["data"];

            echo 'result:'. $resultID;
// END
                $members = new Member;
                $members->muser=$request->muser;
                $members->mpassword=$request->mpassword;
                $members->mname=$request->mname;
                $members->mlastname=$request->mlastname;
                $members->mage=$request->mage;
                $members->msex=$request->msex;
                $members->mbirthday=$request->mbirthday;
                $members->maddress=$request->maddress;
                $members->midcard=$request->midcard;
                $members->mtel=$request->mtel;
                $members->mstart=$request->mstart;
                $members->memail=$request->memail;
                // $members->mreferance=$request->mreferance;
                $members->mtype=$request->mtype;
                $members->did=$request->did;
                $members->hash="";
                $members->mpic='teacher.png';
                $members->uid_rtdb = $resultID;
                $members->save();

               $quota = new quota();
                $quota->mid = $members->mid;
                $quota->type = 1;
                $quota->daytotal = 30;
                $quota->day = 30;
                $quota->year = date("Y");
                $quota->save();

              $quota = new quota();
                $quota->mid = $members->mid;
                $quota->type = 2;
                $quota->daytotal = 5;
                $quota->day = 5;
                $quota->year = date("Y");
                $quota->save();

              $quota = new quota();
                $quota->mid = $members->mid;
                $quota->type = 3;
                $quota->daytotal = 7;
                $quota->day = 7;
                $quota->year = date("Y");
                $quota->save();

              $quota = new quota();
                $quota->mid = $members->mid;
                $quota->type = 4;
                $quota->daytotal = 7;
                $quota->day = 7;
                $quota->year = date("Y");
                $quota->save();
      }
            
        }
        return redirect('user_page')->withErrors ( [ 'เพิ่มข้อมูลเรียบร้อย'] );
      }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)

    {
        $departments = department::all();
        
        $members = Member::where('mid','=',$id)->first();

        $work1  = Work::join('member','work.mid','=','member.mid')->join('detailwork','work.wid','=','detailwork.wid')->where('status','=',"")->get();

        $xs = count($work1);

        return view('admin.edit',compact('members','departments','xs'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request,$id)
    {

     try
     {
       
        if($request->hasFile('mpic')) 
        {
          /*
            delete RTDB Befor update 
            using uid key for use delete child node
            select member table where mid
          */
       $users = DB::table('member')->where('mid','=',$id)->get();
       foreach($users as $user)
       {
          // temp key RTDB 
          $a = $user->uid_rtdb;
          $i = $user->mid;
        }

          // temp key RTDB
         $del = $a;
         $x=$i;

         echo $del;
         echo '<br>';
         echo $x;
                    
        //convert value to string
        $urtdbdel = "?id=$del";

/*
  Delete the specified user resource in firebase realtime database.
  using cloudfunction firebase API using cURL
  delete by key
*/
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://us-central1-nilecon-chat-bot.cloudfunctions.net/deluser" .$urtdbdel,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "DELETE",
  CURLOPT_HTTPHEADER => array(
    "cache-control: no-cache",
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) 
{
  echo "cURL Error #:" . $err;
} 
else 
{
  echo $response;
}
//END Delete

//Insert in to RTDB
          
          /*
            select table where request id
            get name by id 
          */
        $users = DB::table('departmentm')->where('did', $request['did'])->get();
        foreach($users as $user)
        {
          if ($user->did == $request['did']) 
          {
            // temp daname
            $a = $user->dname;
          }
          else 
          {
            echo 'no';
          }
        }

        // temp b
        $b = $a;

        // get convert variable value to string
        $qry_str = "?email=$request->memail&pass=$request->mpassword&firstname=$request->mname&lastname=$request->mlastname&position=$b&phone=$request->mtel";
        echo json_encode($qry_str);
        echo '<br>';

        // cURL using cloudfunctions firebase API
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://us-central1-nilecon-chat-bot.cloudfunctions.net/adduser" .$qry_str,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
          "cache-control: no-cache",
          "postman-token: 8b53e0cf-fefe-6ac8-a1d1-29772576a26c"
        ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) 
        {
          echo "cURL Error #:" . $err;
        } 
        else
        {
          /*
            push to RTDB success
            decode $response and get last uid key pushed for use
          */
          $outputArray = json_decode($response, TRUE);
          var_dump($outputArray);
          echo '<br>';

          // get last uid key pushed
          echo $outputArray["data"];
          echo '<br>'; 
        }
        /* 
          temp last uid key pushed 
          for use to insert to mysql db
        */
        $resultID = $outputArray["data"];
        echo 'result:'. $resultID;
        //END
            
            $file = Input::file('mpic');
            //getting timestamp
            $timestamp = str_replace([' ', ':'], '-', Carbon::now()->toDateTimeString());
            
            $name = $file->getClientOriginalName();
            $user= Member::where('mid',$id)->first();
            $user->mid = $request['mid'];
            $user->muser = $request['muser'];
            $user->mpassword = $request['mpassword'];
            $user->mname = $request['mname'];
            $user->mlastname = $request['mlastname'];
            $user->mage = $request['mage'];
            $user->msex = $request['msex'];
            $user->mbirthday = $request['mbirthday'];
            $user->maddress = $request['maddress'];
            $user->midcard = $request['midcard'];
            $user->mtel = $request['mtel'];
            $user->memail = $request['memail'];
            $user->mstart = $request['mstart'];
            $user->mstatus = $request['mstatus'];
            $user->mtype = $request['mtype'];
            $user->did = $request['did'];
            $user->uid_rtdb = $resultID; //last uid key RTDB
            $user->mpic = $name;
            
            $user->save();
            $file->move(public_path().'/images/', $name);

        }
        else
        {
           /*
            Delete the specified user resource in firebase realtime database.
            using cloudfunction firebase API using cURL
            delete by key
          */
       $users = DB::table('member')->where('mid','=',$id)->get();
       foreach($users as $user)
       {
          $a = $user->uid_rtdb;
          $i = $user->mid;
        }

          // temp del
         $del = $a;
         $x=$i;

         echo $del;
         echo '<br>';
         echo $x;
                    
        //get string value
        $urtdbdel = "?id=$del";

/*
  Delete the specified user resource in firebase realtime database.
  using cloudfunction firebase API
*/
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://us-central1-nilecon-chat-bot.cloudfunctions.net/deluser" .$urtdbdel,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "DELETE",
  CURLOPT_HTTPHEADER => array(
    "cache-control: no-cache",
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}
//END Delete
          
//Insert in to RTDB
          
          /*
            select table where request id
            get name by id 
          */
        $users = DB::table('departmentm')->where('did', $request['did'])->get();
        foreach($users as $user)
        {
          if ($user->did == $request['did']) 
          {
            $a = $user->dname;
          }
          else 
          {
            echo 'no';
          }
        }
        // temp b
        $b = $a;
        
        // get convert variable value to string
        $qry_str = "?email=$request->memail&pass=$request->mpassword&firstname=$request->mname&lastname=$request->mlastname&position=$b&phone=$request->mtel";
        echo json_encode($qry_str);
        echo '<br>';

        // cURL using cloudfunctions firebase API
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://us-central1-nilecon-chat-bot.cloudfunctions.net/adduser" .$qry_str,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
          "cache-control: no-cache",
          "postman-token: 8b53e0cf-fefe-6ac8-a1d1-29772576a26c"
        ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) 
        {
          echo "cURL Error #:" . $err;
        } 
        else
        {
          //decode $response uid key
          $outputArray = json_decode($response, TRUE);
          var_dump($outputArray);
          echo '<br>';
          echo $outputArray["data"];
          echo '<br>'; 
        }
        // temp $result
        $resultID = $outputArray["data"];
        echo 'result:'. $resultID;
//END

            $user= Member::where('mid',$id)->first();
            $user->mid = $request['mid'];
            $user->muser = $request['muser'];
            $user->mpassword = $request['mpassword'];
            $user->mname = $request['mname'];
            $user->mlastname = $request['mlastname'];
            $user->mage = $request['mage'];
            $user->msex = $request['msex'];
            $user->mbirthday = $request['mbirthday'];
            $user->maddress = $request['maddress'];
            $user->midcard = $request['midcard'];
            $user->mtel = $request['mtel'];
            $user->memail = $request['memail'];
            $user->mstart = $request['mstart'];
            $user->mstatus = $request['mstatus'];
            $user->mtype = $request['mtype'];
            $user->did = $request['did'];
            $user->uid_rtdb = $resultID; //uid RTDB
            $user->save();
        }
        //return $user;
        // Save/update user. 
        // This will will update your the row in ur db.
                
        return redirect('user_page')->withErrors ( [ 
            'แก้ไขข้อมูลเรียบร้อย'] );
    }
    catch(ModelNotFoundException $err){
        //Show error page
    }
   
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        /* 
          select member table where mid
       */
       $users = DB::table('member')->where('mid','=',$id)->get();
       foreach($users as $user)
       {  
          // temp key RTDB
          $a = $user->uid_rtdb;
        }

          // temp del
         $del = $a;
                    
        //convert to string
        $urtdbdel = "?id=$del";

 /*
    Delete the specified user resource in firebase realtime database.
    using cloudfunction firebase API using cURL
*/
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://us-central1-nilecon-chat-bot.cloudfunctions.net/deluser" .$urtdbdel,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "DELETE",
  CURLOPT_HTTPHEADER => array(
    "cache-control: no-cache",
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) 
{
  echo "cURL Error #:" . $err;
} 
else 
{
  echo $response;
}

        // MySQL delete
        $works = DB::table('member')->where('mid','=',$id)->delete();
        
        return back()->withErrors ( [ 
            'ทำการลบข้อมูลเรียบร้อย'] );
    }

    public function graphic(Request $request)
    {
        $departments = department::all();
        $members = Member::join('departmentm','member.did','=','departmentm.did')->where('member.did','4')
                    ->orderBy('mstatus');
        $cns = $members->get();
        $countmember = $cns->count();
        $search=$request->Input('search');
        if (!empty($search)) {
            $members->Where('mname','LIKE','%'.$search.'%');
        }

        $work1  = Work::join('member','work.mid','=','member.mid')->join('detailwork','work.wid','=','detailwork.wid')->where('status','=',"")->get();

        $xs = count($work1);

        $members=$members->paginate(10);
        return view('admin.user_page',['members'=>$members,'departments'=>$departments,'count'=>$countmember,'xs'=>$xs]);
        
    }

    public function php(Request $request)
    {
        $departments = department::all();
        $members = Member::join('departmentm','member.did','=','departmentm.did')->where('member.did','5')
                    ->orderBy('mstatus');
        $cns = $members->get();
        $countmember = $cns->count();
        $search=$request->Input('search');
        if (!empty($search)) {
            $members->Where('mname','LIKE','%'.$search.'%');
        }

        $work1  = Work::join('member','work.mid','=','member.mid')->join('detailwork','work.wid','=','detailwork.wid')->where('status','=',"")->get();

        $xs = count($work1);

        $members=$members->paginate(10);
        return view('admin.user_page',['members'=>$members,'departments'=>$departments,'count'=>$countmember,'xs'=>$xs]);
        
    }

    public function c(Request $request)
    {
        $departments = department::all();
        $members = Member::join('departmentm','member.did','=','departmentm.did')->where('member.did','3')
                    ->orderBy('mstatus');
        $cns = $members->get();
        $countmember = $cns->count();
        $search=$request->Input('search');
        if (!empty($search)) {
            $members->Where('mname','LIKE','%'.$search.'%');
        }

        $work1  = Work::join('member','work.mid','=','member.mid')->join('detailwork','work.wid','=','detailwork.wid')->where('status','=',"")->get();

        $xs = count($work1);

        $members=$members->paginate(10);
        return view('admin.user_page',['members'=>$members,'departments'=>$departments,'count'=>$countmember,'xs'=>$xs]);
        
    }

    public function ios(Request $request)
    {
        $departments = department::all();
        $members = Member::join('departmentm','member.did','=','departmentm.did')->where('member.did','2')
                    ->orderBy('mstatus');
        $cns = $members->get();
        $countmember = $cns->count();
        $search=$request->Input('search');
        if (!empty($search)) {
            $members->Where('mname','LIKE','%'.$search.'%');
        }

        $work1  = Work::join('member','work.mid','=','member.mid')->join('detailwork','work.wid','=','detailwork.wid')->where('status','=',"")->get();

        $xs = count($work1);

        $members=$members->paginate(10);
        return view('admin.user_page',['members'=>$members,'departments'=>$departments,'count'=>$countmember,'xs'=>$xs]);
        
    }

    public function andriod(Request $request)
    {
        $departments = department::all();
        $members = Member::join('departmentm','member.did','=','departmentm.did')->where('member.did','1')
                    ->orderBy('mstatus');
        $cns = $members->get();
        $countmember = $cns->count();
        $search=$request->Input('search');
        if (!empty($search)) {
            $members->Where('mname','LIKE','%'.$search.'%');
        }

        $work1  = Work::join('member','work.mid','=','member.mid')->join('detailwork','work.wid','=','detailwork.wid')->where('status','=',"")->get();

        $xs = count($work1);

        $members=$members->paginate(10);
       return view('admin.user_page',['members'=>$members,'departments'=>$departments,'count'=>$countmember,'xs'=>$xs]);
        
    }

     public function customer_service(Request $request)
    {
        $departments = department::all();
        $members = Member::join('departmentm','member.did','=','departmentm.did')->where('member.did','19')
                    ->orderBy('mstatus');
        $cns = $members->get();
        $countmember = $cns->count();
        $search=$request->Input('search');
        if (!empty($search)) {
            $members->Where('mname','LIKE','%'.$search.'%');
        }

        $work1  = Work::join('member','work.mid','=','member.mid')->join('detailwork','work.wid','=','detailwork.wid')->where('status','=',"")->get();

        $xs = count($work1);

        $members=$members->paginate(10);
        return view('admin.user_page',['members'=>$members,'departments'=>$departments,'count'=>$countmember,'xs'=>$xs]);
    }

    public function callcenter(Request $request)
    {
        $departments = department::all();
        $members = Member::join('departmentm','member.did','=','departmentm.did')->where('member.did','18')
                    ->orderBy('mstatus');
        $cns = $members->get();
        $countmember = $cns->count();
        $search=$request->Input('search');
        if (!empty($search)) {
            $members->Where('mname','LIKE','%'.$search.'%');
        }

        $work1  = Work::join('member','work.mid','=','member.mid')->join('detailwork','work.wid','=','detailwork.wid')->where('status','=',"")->get();

        $xs = count($work1);

        $members=$members->paginate(10);
        return view('admin.user_page',['members'=>$members,'departments'=>$departments,'count'=>$countmember,'xs'=>$xs]);
        
    }

    public function AE(Request $request)
    {
        $departments = department::all();
        $members = Member::join('departmentm','member.did','=','departmentm.did')->where('member.did','18')
                    ->orderBy('mstatus');
        $cns = $members->get();
        $countmember = $cns->count();
        $search=$request->Input('search');
        if (!empty($search)) {
            $members->Where('mname','LIKE','%'.$search.'%');
        }

        $work1  = Work::join('member','work.mid','=','member.mid')->join('detailwork','work.wid','=','detailwork.wid')->where('status','=',"")->get();

        $xs = count($work1);

        $members=$members->paginate(10);
        return view('admin.user_page',['members'=>$members,'departments'=>$departments,'count'=>$countmember,'xs'=>$xs]);
        
    }

    public function other(Request $request)
    {
        
        $id_list = [23,27,28,29,30];
        
       
        $departments = department::all();
        $members = Member::join('departmentm','member.did','=','departmentm.did')->where('member.did',$id_list)
                    ->orderBy('mstatus');

        $cns = $members->get();
        $countmember = $cns->count();
        $search=$request->Input('search');
        if (!empty($search)) {
            $members->Where('mname','LIKE','%'.$search.'%');
        }

        $work1  = Work::join('member','work.mid','=','member.mid')->join('detailwork','work.wid','=','detailwork.wid')->where('status','=',"")->get();

        $xs = count($work1);

        $members=$members->paginate(10);
        //return $members;
        return view('admin.user_page',['members'=>$members,'departments'=>$departments,'count'=>$countmember,'xs'=>$xs]);

    }
        public function insert(Request $request)
        {
          $android->dname = $request['android'];
          $ios->dname = $request['ios'];
          $c->dname = $request['ios'];
        }
}