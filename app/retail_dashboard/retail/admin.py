# retail/admin.py
from django.contrib import admin
from .models import *

@admin.register(ОбобщеннаяГруппаТоваров)
class ОбобщеннаяГруппаТоваровAdmin(admin.ModelAdmin):
    list_display = ['наименование', 'created_at']
    search_fields = ['наименование']

@admin.register(Группа)
class ГруппаAdmin(admin.ModelAdmin):
    list_display = ['наименование', 'id_обобщ_группа', 'created_at']
    list_filter = ['id_обобщ_группа']
    search_fields = ['наименование']

@admin.register(Артикулы)
class АртикулыAdmin(admin.ModelAdmin):
    list_display = ['артикул', 'наименование', 'id_группы', 'created_at']
    list_filter = ['id_группы']
    search_fields = ['артикул', 'наименование']

@admin.register(ЕдиницыИзмерения)
class ЕдиницыИзмеренияAdmin(admin.ModelAdmin):
    list_display = ['наименование', 'сокращение', 'created_at']
    search_fields = ['наименование']

@admin.register(Города)
class ГородаAdmin(admin.ModelAdmin):
    list_display = ['город', 'экономический_район', 'федеральный_округ', 'created_at']
    list_filter = ['федеральный_округ', 'экономический_район']
    search_fields = ['город']

@admin.register(ТипыКлиентов)
class ТипыКлиентовAdmin(admin.ModelAdmin):
    list_display = ['наименование', 'приоритет', 'created_at']
    list_filter = ['приоритет']

@admin.register(Скидки)
class СкидкиAdmin(admin.ModelAdmin):
    list_display = ['дата_продажи', 'id_город', 'id_типы_клиентов', 'общая_скидка', 'created_at']
    list_filter = ['дата_продажи', 'id_город', 'id_типы_клиентов']

@admin.register(Клиенты)
class КлиентыAdmin(admin.ModelAdmin):
    list_display = ['дата_продажи', 'номер_клиента', 'id_артикулы', 'сумма_со_скидкой', 'общая_скидка_процент']
    list_filter = ['дата_продажи', 'id_группа']
    search_fields = ['номер_клиента']
    readonly_fields = ['сумма_без_скидки', 'общая_скидка_процент', 'сумма_со_скидкой']