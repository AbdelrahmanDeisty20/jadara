
{{--
here content of message send to user email
--}}
<h1>Hello {{ $user->name }} 🎉</h1>
<p>You have created a new account on our site successfully.</p>
<p><strong>Your code:</strong> {{ $user->verification_code }}</p>
<p>Thanks for joining our site!</p>