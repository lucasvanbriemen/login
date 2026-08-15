class SessionsController < ApplicationController
  # Native clients (the React Native app) post credentials as JSON and have no
  # browser session to carry a CSRF token in — the token they get back *is*
  # their credential. Browser form posts keep the check.
  skip_forgery_protection if: -> { request.format.json? }

  def new
  end

  def create
    account = Account.find_by(email: session_params[:email])

    return deny_login unless account&.authenticate(session_params[:password])

    token = account.tokens.create!(
      value: SecureRandom.hex(32),
      expires_at: Time.current + Token::TOKEN_DURATION
    )

    respond_to do |format|
      # No cookie for JSON callers: they hold the token themselves, and the
      # expiry goes with it so the client can log out on its own once the week
      # is up instead of waiting to be refused.
      format.json do
        render json: {
          token: token.value,
          expires_at: token.expires_at,
          account: account.as_json(except: :password_digest).merge(permissions: account.permissions)
        }
      end

      format.html do
        cookies[:auth_token] = {
          value: token.value,
          expires: token.expires_at,
          httponly: true,
          secure: Rails.env.production?,
          domain: :all
        }

        path = session_params[:redirect_to].presence || root_path

        redirect_to path.to_s + "?auth_token=#{token.value}", notice: "Logged in successfully", allow_other_host: true
      end
    end
  end

  # Routed since the beginning but never implemented. Revoking the token is the
  # only way to end a session early: it is valid for a week and never refreshed.
  def destroy
    Token.find_by(value: revocable_token)&.destroy
    cookies.delete(:auth_token, domain: :all)

    respond_to do |format|
      format.json { head :no_content }
      format.html { redirect_to root_path, notice: "Logged out" }
    end
  end

  def show
    token = Token.find_by(value: params[:token])

    if token.nil? || token.expires_at.past?
      return render json: { isloggedin: false, permissions: Permission::BASE }
    end

    account = token.account

    render json: account.as_json(except: :password_digest).merge(
      permissions: account.permissions,
      isloggedin: true
    )
  end

  private

  def deny_login
    respond_to do |format|
      format.json { render json: { error: "Invalid email or password" }, status: :unauthorized }
      format.html do
        flash.now[:alert] = "Invalid email or password"
        render :new, status: :unprocessable_entity
      end
    end
  end

  # The browser keeps its token in a cookie; native clients send the one they
  # were handed at login back as a bearer token.
  def revocable_token
    cookies[:auth_token].presence || request.headers["Authorization"].to_s[/\ABearer (.+)\z/, 1]
  end

  def session_params
    params.fetch(:session, {}).permit(:email, :password, :redirect_to)
  end
end
