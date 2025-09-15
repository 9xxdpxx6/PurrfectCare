@extends('layouts.client')

@section('title', 'Подтверждение записи - PurrfectCare')

@section('content')
<!-- Hero Section -->
<section class="bg-primary text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-4">Подтверждение записи</h1>
                <p class="lead">
                    Проверьте данные и подтвердите запись на прием
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Progress Steps -->
<section class="py-4 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-center">
                    <div class="d-flex align-items-center flex-wrap justify-content-center">
                        <div class="step completed">
                            <div class="step-number">1</div>
                            <div class="step-label d-none d-sm-block">Филиал</div>
                        </div>
                        <div class="step-line completed"></div>
                        <div class="step completed">
                            <div class="step-number">2</div>
                            <div class="step-label d-none d-sm-block">Ветеринар</div>
                        </div>
                        <div class="step-line completed"></div>
                        <div class="step completed">
                            <div class="step-number">3</div>
                            <div class="step-label d-none d-sm-block">Время</div>
                        </div>
                        <div class="step-line completed"></div>
                        <div class="step active">
                            <div class="step-number">4</div>
                            <div class="step-label d-none d-sm-block">Подтверждение</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Confirmation Form -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-5">
                        <h4 class="card-title mb-4">Данные записи</h4>
                        
                        <!-- Appointment Details -->
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <div class="info-card">
                                    <h6 class="text-primary mb-2">
                                        <i class="bi bi-building me-2"></i>Филиал
                                    </h6>
                                    <p class="mb-0">{{ $branch->name }}</p>
                                    <small class="text-muted">{{ $branch->address }}</small>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="info-card">
                                    <h6 class="text-primary mb-2">
                                        <i class="bi bi-person me-2"></i>Ветеринар
                                    </h6>
                                    <p class="mb-0">{{ $veterinarian->name }}</p>
                                    <small class="text-muted">Ветеринар</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <div class="info-card">
                                    <h6 class="text-primary mb-2">
                                        <i class="bi bi-calendar me-2"></i>Дата и время
                                    </h6>
                                    <p class="mb-0">{{ $datetime->format('d.m.Y') }}</p>
                                    <small class="text-muted">{{ $datetime->format('H:i') }} - {{ $datetime->addMinutes(30)->format('H:i') }}</small>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="info-card">
                                    <h6 class="text-primary mb-2">
                                        <i class="bi bi-clock me-2"></i>Длительность
                                    </h6>
                                    <p class="mb-0">30 минут</p>
                                    <small class="text-muted">Стандартная длительность приема</small>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- Pet Selection -->
                        <div class="mb-4">
                            <h5 class="mb-3">Выберите питомца</h5>
                            @if($pets->count() > 0)
                                <div class="row g-3">
                                    @foreach($pets as $pet)
                                    <div class="col-12 col-md-6">
                                        <div class="pet-card" data-pet-id="{{ $pet->id }}">
                                            <div class="pet-avatar">
                                                <i class="bi bi-heart"></i>
                                            </div>
                                            <div class="pet-info">
                                                <h6 class="pet-name">{{ $pet->name }}</h6>
                                                <small class="text-muted">{{ $pet->breed->name ?? 'Порода не указана' }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    У вас нет зарегистрированных питомцев. 
                                    <a href="#" class="alert-link">Добавить питомца</a>
                                    <br><small class="text-muted">Вы можете записаться на прием без выбора питомца</small>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Complaints -->
                        <div class="mb-4">
                            <label for="complaints" class="form-label">Жалобы и симптомы (необязательно)</label>
                            <textarea class="form-control" id="complaints" name="complaints" rows="3" 
                                      placeholder="Опишите состояние питомца, жалобы или симптомы..."></textarea>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="d-flex flex-column flex-md-row gap-3 justify-content-between">
                            <a href="{{ route('client.appointment.time', [
                                'branch_id' => $branch->id,
                                'veterinarian_id' => $veterinarian->id
                            ]) }}" class="btn btn-outline-secondary order-2 order-md-1">
                                <i class="bi bi-arrow-left me-2"></i>Назад
                            </a>
                            
                            <button type="button" class="btn btn-primary btn-lg order-1 order-md-2" id="confirmAppointment">
                                <i class="bi bi-check-circle me-2"></i>Подтвердить запись
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Hidden Form -->
<form id="appointmentForm" method="POST" action="{{ route('client.appointment.store') }}" style="display: none;">
    @csrf
    <input type="hidden" name="branch_id" value="{{ $branch->id }}">
    <input type="hidden" name="veterinarian_id" value="{{ $veterinarian->id }}">
    <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
    <input type="hidden" name="time" value="{{ $datetime->format('H:i') }}">
    <input type="hidden" name="pet_id" id="selectedPetId">
    <input type="hidden" name="complaints" id="complaintsInput">
</form>
@endsection

@push('styles')
<style>

.info-card {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    border-left: 4px solid #007bff;
    margin-bottom: 1rem;
}

@media (max-width: 767.98px) {
    .info-card {
        padding: 20px;
        margin-bottom: 1.5rem;
    }
}

.pet-card {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    display: flex;
    align-items: center;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    background: white;
    margin-bottom: 1rem;
}

@media (max-width: 767.98px) {
    .pet-card {
        padding: 20px;
        margin-bottom: 1.5rem;
    }
}

.pet-card:hover {
    border-color: #007bff;
    background-color: #f8f9fa;
}

.pet-card.selected {
    border-color: #007bff;
    background-color: #e3f2fd;
}

.pet-avatar {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #ff6b6b, #ee5a52);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    margin-right: 15px;
}

.pet-info {
    flex: 1;
}

.pet-name {
    margin: 0;
    color: #2c3e50;
    font-weight: 600;
}

.card-title {
    color: #2c3e50;
    font-weight: 600;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const petCards = document.querySelectorAll('.pet-card');
    const confirmButton = document.getElementById('confirmAppointment');
    const appointmentForm = document.getElementById('appointmentForm');
    const selectedPetIdInput = document.getElementById('selectedPetId');
    const complaintsInput = document.getElementById('complaintsInput');
    const complaintsTextarea = document.getElementById('complaints');
    
    let selectedPetId = null;
    
    // Выбор питомца
    petCards.forEach(card => {
        card.addEventListener('click', function() {
            // Убираем выделение с других карточек
            petCards.forEach(c => c.classList.remove('selected'));
            
            // Выделяем текущую карточку
            this.classList.add('selected');
            
            // Сохраняем ID выбранного питомца
            selectedPetId = this.dataset.petId;
        });
    });
    
    // Подтверждение записи
    confirmButton.addEventListener('click', function() {
        // Заполняем скрытую форму
        if (selectedPetId && selectedPetId !== '') {
            selectedPetIdInput.value = parseInt(selectedPetId);
        } else {
            selectedPetIdInput.removeAttribute('value');
        }
        complaintsInput.value = complaintsTextarea.value;
        
        // Отладочная информация
        console.log('Отправка формы:', {
            pet_id: selectedPetIdInput.value,
            pet_id_type: typeof selectedPetIdInput.value,
            complaints: complaintsInput.value,
            branch_id: document.querySelector('input[name="branch_id"]').value,
            veterinarian_id: document.querySelector('input[name="veterinarian_id"]').value,
            schedule_id: document.querySelector('input[name="schedule_id"]').value,
            time: document.querySelector('input[name="time"]').value
        });
        
        // Отправляем форму
        appointmentForm.submit();
    });
});
</script>
@endpush
