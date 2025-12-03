import os
from datetime import datetime
from functools import wraps

from flask import Flask, render_template, request, redirect, url_for, session, flash
from flask_sqlalchemy import SQLAlchemy
from werkzeug.security import generate_password_hash, check_password_hash


app = Flask(__name__)
app.config['SECRET_KEY'] = os.environ.get('SECRET_KEY', 'development-secret-key')
app.config['SQLALCHEMY_DATABASE_URI'] = os.environ.get('DATABASE_URL', 'sqlite:///eventos.db')
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False

db = SQLAlchemy(app)


class User(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    username = db.Column(db.String(80), unique=True, nullable=False)
    password_hash = db.Column(db.String(255), nullable=False)
    events = db.relationship('Event', backref='creator', lazy=True)

    def set_password(self, password: str) -> None:
        self.password_hash = generate_password_hash(password)

    def check_password(self, password: str) -> bool:
        return check_password_hash(self.password_hash, password)


class Event(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    title = db.Column(db.String(150), nullable=False)
    city = db.Column(db.String(100), nullable=False)
    description = db.Column(db.Text, nullable=False)
    start_datetime = db.Column(db.DateTime, nullable=False)
    end_datetime = db.Column(db.DateTime)
    is_multi_day = db.Column(db.Boolean, default=False)
    is_paid = db.Column(db.Boolean, default=False)
    price_info = db.Column(db.String(120))
    created_by = db.Column(db.Integer, db.ForeignKey('user.id'), nullable=False)

    def schedule_label(self) -> str:
        if self.is_multi_day and self.end_datetime:
            return f"{self.start_datetime:%d/%m/%Y %H:%M} até {self.end_datetime:%d/%m/%Y %H:%M}"
        return f"{self.start_datetime:%d/%m/%Y %H:%M}"

    def paid_label(self) -> str:
        if self.is_paid and self.price_info:
            return f"Pago — {self.price_info}"
        if self.is_paid:
            return "Pago"
        return "Gratuito"


def current_user():
    user_id = session.get('user_id')
    if user_id:
        return User.query.get(user_id)
    return None


def login_required(func):
    @wraps(func)
    def wrapper(*args, **kwargs):
        if not current_user():
            flash('Você precisa estar logado para realizar esta ação.', 'warning')
            return redirect(url_for('login', next=request.path))
        return func(*args, **kwargs)
    return wrapper


@app.before_request
def create_tables():
    db.create_all()


@app.route('/')
def index():
    events = Event.query.order_by(Event.start_datetime.asc()).all()
    return render_template('index.html', events=events, user=current_user())


@app.route('/register', methods=['GET', 'POST'])
def register():
    if request.method == 'POST':
        username = request.form['username'].strip()
        password = request.form['password']

        if not username or not password:
            flash('Informe um usuário e senha.', 'danger')
            return redirect(url_for('register'))

        if User.query.filter_by(username=username).first():
            flash('Usuário já existe.', 'danger')
            return redirect(url_for('register'))

        user = User(username=username)
        user.set_password(password)
        db.session.add(user)
        db.session.commit()
        flash('Cadastro realizado com sucesso! Faça login.', 'success')
        return redirect(url_for('login'))

    return render_template('register.html', user=current_user())


@app.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        username = request.form['username']
        password = request.form['password']
        user = User.query.filter_by(username=username).first()
        if user and user.check_password(password):
            session['user_id'] = user.id
            flash('Login realizado com sucesso!', 'success')
            next_page = request.args.get('next') or url_for('index')
            return redirect(next_page)
        flash('Usuário ou senha inválidos.', 'danger')
        return redirect(url_for('login'))

    return render_template('login.html', user=current_user())


@app.route('/logout')
def logout():
    session.clear()
    flash('Sessão encerrada.', 'info')
    return redirect(url_for('index'))


@app.route('/event/new', methods=['GET', 'POST'])
@login_required
def create_event():
    if request.method == 'POST':
        title = request.form['title'].strip()
        city = request.form['city'].strip()
        description = request.form['description'].strip()
        start_datetime = datetime.fromisoformat(request.form['start_datetime'])
        end_datetime_raw = request.form.get('end_datetime')
        is_multi_day = request.form.get('is_multi_day') == 'on'
        is_paid = request.form.get('is_paid') == 'on'
        price_info = request.form.get('price_info', '').strip() or None
        end_datetime = datetime.fromisoformat(end_datetime_raw) if end_datetime_raw else None

        if not title or not city or not description:
            flash('Preencha todos os campos obrigatórios.', 'danger')
            return redirect(url_for('create_event'))

        if is_multi_day and (not end_datetime or end_datetime <= start_datetime):
            flash('Informe uma data final posterior à data inicial para eventos de vários dias.', 'danger')
            return redirect(url_for('create_event'))

        event = Event(
            title=title,
            city=city,
            description=description,
            start_datetime=start_datetime,
            end_datetime=end_datetime,
            is_multi_day=is_multi_day,
            is_paid=is_paid,
            price_info=price_info,
            created_by=current_user().id,
        )
        db.session.add(event)
        db.session.commit()
        flash('Evento criado com sucesso!', 'success')
        return redirect(url_for('index'))

    return render_template('event_form.html', user=current_user())


@app.route('/event/<int:event_id>')
def event_detail(event_id):
    event = Event.query.get_or_404(event_id)
    return render_template('event_detail.html', event=event, user=current_user())


@app.route('/event/<int:event_id>/edit', methods=['GET', 'POST'])
@login_required
def edit_event(event_id):
    event = Event.query.get_or_404(event_id)

    if request.method == 'POST':
        event.title = request.form['title'].strip()
        event.city = request.form['city'].strip()
        event.description = request.form['description'].strip()
        event.start_datetime = datetime.fromisoformat(request.form['start_datetime'])
        end_datetime_raw = request.form.get('end_datetime')
        event.is_multi_day = request.form.get('is_multi_day') == 'on'
        event.is_paid = request.form.get('is_paid') == 'on'
        event.price_info = request.form.get('price_info', '').strip() or None
        event.end_datetime = datetime.fromisoformat(end_datetime_raw) if end_datetime_raw else None

        if event.is_multi_day and (not event.end_datetime or event.end_datetime <= event.start_datetime):
            flash('Informe uma data final posterior à data inicial para eventos de vários dias.', 'danger')
            return redirect(url_for('edit_event', event_id=event.id))

        db.session.commit()
        flash('Evento atualizado!', 'success')
        return redirect(url_for('event_detail', event_id=event.id))

    return render_template('event_form.html', event=event, user=current_user())


@app.route('/event/<int:event_id>/delete', methods=['POST'])
@login_required
def delete_event(event_id):
    event = Event.query.get_or_404(event_id)
    db.session.delete(event)
    db.session.commit()
    flash('Evento removido.', 'info')
    return redirect(url_for('index'))


if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0')
