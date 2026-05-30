class SessionsController < ApplicationController
  def new
  end

  def create
    account = Account.find_by(email: params[:email])

    if account&.authenticate(params[:password])
      session[:account_id] = account.id
      redirect_to root_path, notice: "Logged in successfully"
    else
      flash.now[:alert] = "Invalid email or password"
      render :new, status: :unprocessable_entity
    end
  end
end
