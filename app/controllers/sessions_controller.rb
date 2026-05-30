class SessionsController < ApplicationController
  def new
  end

  def create
    account = Account.find_by(email: params[:email])

    if !account&.authenticate(params[:password])
      flash.now[:alert] = "Invalid email or password"
      return render :new, status: :unprocessable_entity
    end

    token = account.tokens.create!(
      value: SecureRandom.hex(32),
      expires_at: Time.current + Token::TOKEN_DURATION
    )

    # Store the token value in a cookie for authentication in subsequent requests
    cookies[:auth_token] = {
      value: token.value,
      expires: token.expires_at,
      httponly: true,
      secure: Rails.env.production?
    }

    session[:account_id] = account.id
    redirect_to root_path, notice: "Logged in successfully"
  end

  def show
    token = Token.find_by(value: params[:token])

    if token.nil? || token.expires_at.past?
      return render json: { error: "Invalid or expired token" }, status: :unauthorized
    end

    render json: {
      account: {
        id: token.account.id,
        email: token.account.email,
        name: token.account.name
      }
    }
  end
end
