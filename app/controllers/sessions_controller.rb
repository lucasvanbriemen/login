class SessionsController < ApplicationController
  def new
  end

  def create
    account = Account.find_by(email: params[:email])

    if !account&.authenticate(params[:password])
      flash.now[:alert] = "Invalid email or password"
      render :new, status: :unprocessable_entity
    end

    token = account.tokens.create!(
      value: SecureRandom.hex(32),
      expires_at: Time.current + Token::TOKEN_DURATION
    )

    # Store the token value in a cookie for authentication in subsequent requests
    cookies.encrypted[:auth_token] = {
      value: token.value,
      expires: token.expires_at,
      httponly: true,
      secure: Rails.env.production?
    }

    session[:account_id] = account.id
    redirect_to root_path, notice: "Logged in successfully"
  end
end
