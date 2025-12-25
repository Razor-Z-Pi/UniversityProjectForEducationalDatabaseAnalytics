# retail/forms.py
from django import forms
from .models import *

class ProductForm(forms.ModelForm):
    class Meta:
        model = Артикулы
        fields = ['артикул', 'наименование', 'id_группы']
        widgets = {
            'артикул': forms.TextInput(attrs={'class': 'form-control'}),
            'наименование': forms.TextInput(attrs={'class': 'form-control'}),
            'id_группы': forms.Select(attrs={'class': 'form-control'}),
        }

class SaleForm(forms.ModelForm):
    class Meta:
        model = Клиенты
        fields = [
            'дата_продажи', 'номер_клиента', 'id_группа', 
            'id_ед_измерения', 'id_артикулы', 'id_скидки',
            'цена_за_ед', 'количество'
        ]
        widgets = {
            'дата_продажи': forms.DateInput(attrs={'class': 'form-control', 'type': 'date'}),
            'номер_клиента': forms.TextInput(attrs={'class': 'form-control'}),
            'id_группа': forms.Select(attrs={'class': 'form-control'}),
            'id_ед_измерения': forms.Select(attrs={'class': 'form-control'}),
            'id_артикулы': forms.Select(attrs={'class': 'form-control'}),
            'id_скидки': forms.Select(attrs={'class': 'form-control'}),
            'цена_за_ед': forms.NumberInput(attrs={'class': 'form-control', 'step': '0.01'}),
            'количество': forms.NumberInput(attrs={'class': 'form-control', 'step': '0.001'}),
        }

class DiscountForm(forms.ModelForm):
    class Meta:
        model = Скидки
        fields = ['дата_продажи', 'id_город', 'id_типы_клиентов', 
                 'скидки_по_группе_клиентов', 'скидки_по_группе_сумме_покупки']
        widgets = {
            'дата_продажи': forms.DateInput(attrs={'class': 'form-control', 'type': 'date'}),
            'id_город': forms.Select(attrs={'class': 'form-control'}),
            'id_типы_клиентов': forms.Select(attrs={'class': 'form-control'}),
            'скидки_по_группе_клиентов': forms.NumberInput(attrs={'class': 'form-control', 'step': '0.01'}),
            'скидки_по_группе_сумме_покупки': forms.NumberInput(attrs={'class': 'form-control', 'step': '0.01'}),
        }