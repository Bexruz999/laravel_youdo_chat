<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use RTippin\Messenger\Exceptions\InvalidProviderException;
use RTippin\Messenger\Exceptions\MessengerComposerException;
use RTippin\Messenger\Facades\MessengerComposer;
use RTippin\Messenger\Models\Friend;
use RTippin\Messenger\Models\Messenger;
use RTippin\Messenger\Models\Participant;
use RTippin\Messenger\Models\Thread;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default, this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected string $redirectTo = RouteServiceProvider::HOME;

    /**
     * @var User
     */
    private User $newUser;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     *
     * @throws Throwable
     */
    protected function create(array $data): User
    {
        try {
            DB::transaction(fn () => $this->makeUser($data));
        } catch (Throwable $e) {
            report($e);

            throw new HttpException(500, 'Registration failed.');
        }

        return $this->newUser;
    }

    /**
     * @param  array  $data
     *
     * @throws InvalidProviderException|MessengerComposerException|Throwable
     */
    private function makeUser(array $data): void
    {
        $this->newUser = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'demo' => false,
            'admin' => false,
            'password' => Hash::make($data['password']),
        ]);

        Messenger::factory()->owner($this->newUser)->create();

        // Remove this method call if you do not want new
        // users to be setup with the admin account.
        $this->setupUserWithDemo();
    }

    /**
     * @throws Throwable
     * @throws InvalidProviderException|MessengerComposerException
     */
    private function setupUserWithDemo(): void
    {
        $admin = User::whereEmail(DatabaseSeeder::Admin['email'])->first();
        $admin2 = User::whereEmail(DatabaseSeeder::Admin2['email'])->first();
        Friend::factory()->providers($admin, $this->newUser)->create();
        Friend::factory()->providers($admin2, $this->newUser)->create();
        Friend::factory()->providers($this->newUser, $admin)->create();
        Friend::factory()->providers($this->newUser, $admin2)->create();
        //$group = Thread::group()->oldest()->first();
        /*Participant::factory()->for($group)->owner($this->newUser)->create([
            'start_calls' => true,
            'send_knocks' => true,
            'add_participants' => true,
            'manage_invites' => true,
        ]);*/

        MessengerComposer::to($this->newUser)
            ->from($admin)
            ->form(' <div class="password_block">
    <form id="registeration_form" method="POST" action="">
      <div class="error-text"></div>

        <input type="hidden" name="_token" value="'. csrf_token() .'">

      <div class="field">
          <label class="label">Ism</label>
        <input type="text" name="name" class="form-control" id="formGroupExampleInput" placeholder="ism"
          required="required" minlength="5">
      </div>

      <div class="field">
      <label class="label">email</label>
        <input type="email" name="email" class="form-control" id="formGroupExampleInput2" placeholder="email"
          required="required">
      </div>

      <div class="field">
      <label class="label">telfon raqam</label>
        <input onkeyup="active()" type="tel" name="phone" class="form-control" id="formGroupExampleInput3" placeholder="+998123456789"
          required="required"
          pattern="^((8|\+374|\+994|\+995|\+375|\+7|\+380|\+38|\+996|\+998|\+993)[\- ]?)?\(?\d{3,5}\)?[\- ]?\d{1}[\- ]?\d{1}[\- ]?\d{1}[\- ]?\d{1}[\- ]?\d{1}(([\- ]?\d{1})?[\- ]?\d{1})?$">
      </div>

      <div class="field">
      <label class="label">Enter Password</label>
        <input onkeyup="active()" id="password" class="form-control" type="password" placeholder="Enter Password">
      </div>

      <div class="field">
      <label class="label">Confirm Password</label>
        <input onkeyup="active_2()" id="confirmPassword" class="form-control" type="password" placeholder="Confirm Password">
        <div class="show">SHOW</div>
      </div>
      
      <button class="form-button" disabled>Check</button>
    </form>


    <script>
      const password = document.querySelector("#password");
      const confirmPassword = document.querySelector("#confirmPassword");
      const errorText = document.querySelector(".error-text");
      const showBtn = document.querySelector(".show");
      const btn = document.querySelector("button");
      function active() {
        if (password.value.length >= 8) {
          btn.classList.add("active");
          confirmPassword.removeAttribute("disabled", "");
        } else {
          btn.setAttribute("disabled", "");
          btn.classList.remove("active");
        }
      }
      btn.onclick = function () {
        if (password.value != confirmPassword.value) {
          errorText.style.display = "block";
          errorText.classList.remove("matched");
          errorText.textContent = "Parolni togri kiriting!";
          return false;
        } else {
          errorText.style.display = "block";
          errorText.classList.add("matched");
          errorText.textContent = "Yuborildi";
          return false;
        }
      }
      function active_2() {
        if (confirmPassword.value != "") {
          showBtn.style.display = "block";
          showBtn.onclick = function () {
            if ((password.type == "password") && (confirmPassword.type == "password")) {
              password.type = "text";
              confirmPassword.type = "text";
              this.textContent = "Hide";
              this.classList.add("active");
            } else {
              password.type = "password";
              confirmPassword.type = "password";
              this.textContent = "Show";
              this.classList.remove("active");
            }
          }
        } else {
          showBtn.style.display = "none";
        }
      }
      
    </script>
    <style>
      @import url("https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap");

      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: "Poppins", sans-serif;
      }

      .password_block,
      .password_block:disabled {
        max-width: 300px;
        border-radius: 15px;
        background: #fff;
      }

      .password_block header {
        font-size: 25px;
        font-weight: 600;
        line-height: 33px;
      }

      .password_block form {
        margin: 0px 8px;
      }

      .password_block form .error-text {
        padding: 8px 0;
        border-radius: 5px;
        color: #8B3E46;
        display: none;
      }

      .password_block form .error-text.matched {
        color: #588C64;
      }

      .password_block form .field {
        width: 100%;
        position: relative;
        padding-top: 15px;
      }
      
      .password_block form .field .label {
        position: absolute;
        top: 0;
        font-size: 12px;
      }

      form .field input {
        width: 100%;
        height: 100%;
        padding: 0 10px;
        outline: none;
        padding: 5px 0px;
        border-radius: 0px;
        transition: all 0.3s;
        background: transparent;
        border: none;
        border-bottom: 1px solid #dadada;
      }

      form .field input::placeholder {
        font-size: 15px;
      }

      form .field input:active,
      form .field input:focus,
      form .field input:hover{
        border-bottom: 1px solid #1890ff;
      }

      form .field input:valid {
      }

      form .field .show {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 14px;
        font-weight: 600;
        user-select: none;
        cursor: pointer;
        display: none;
      }

      form .field .show.active {
        color: #0088dd;
      }

      .form-button {
        width: 100%;
        height: 45px;
        margin: 3px 0 10px 0;
        border: none;
        outline: none;
        background: #0088dd;
        border-radius: 5px;
        color: #fff;
        font-size: 18px;
        font-weight: 500;
        letter-spacing: 1px;
        text-transform: uppercase;
        cursor: no-drop;
        opacity: 0.7;
      }

      .form-button.active {
        cursor: pointer;
        opacity: 1;
        transition: all 0.3s;
      }

      .form-button.active:hover {
        background: #0088dd;
      }
    </style>
  </div>');

        MessengerComposer::to($this->newUser)
            ->from($admin2)
            ->message('Welcome to the messenger demo!');
    }
}


