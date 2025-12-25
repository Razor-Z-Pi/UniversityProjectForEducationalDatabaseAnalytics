# retail/views.py
from django.shortcuts import render, redirect, get_object_or_404
from django.http import JsonResponse
from django.db.models import Sum, Count, Avg, F, Q
from django.db import connection
from datetime import datetime, timedelta
import json
from .models import *
from .forms import *

def index(request):
    return redirect('dashboard')

def dashboard(request):
    # Основная статистика
    total_sales = Клиенты.objects.count()
    total_revenue = Клиенты.objects.aggregate(total=Sum('сумма_со_скидкой'))['total'] or 0
    avg_discount = Клиенты.objects.aggregate(avg=Avg('общая_скидка_процент'))['avg'] or 0
    
    # Последние продажи
    recent_sales = Клиенты.objects.select_related(
        'id_артикулы', 'id_скидки'
    ).order_by('-дата_продажи')[:10]
    
    # Продажи по дням (последние 7 дней)
    end_date = datetime.now().date()
    start_date = end_date - timedelta(days=7)
    
    daily_sales = Клиенты.objects.filter(
        дата_продажи__gte=start_date,
        дата_продажи__lte=end_date
    ).values('дата_продажи').annotate(
        total_sales=Sum('сумма_со_скидкой'),
        count_sales=Count('id')
    ).order_by('дата_продажи')
    
    # Топ товаров
    top_products = Артикулы.objects.annotate(
        total_sold=Sum('клиенты__количество'),
        total_revenue=Sum('клиенты__сумма_со_скидкой')
    ).order_by('-total_revenue')[:5]
    
    context = {
        'total_sales': total_sales,
        'total_revenue': total_revenue,
        'avg_discount': round(avg_discount, 2),
        'recent_sales': recent_sales,
        'daily_sales': list(daily_sales),
        'top_products': top_products,
    }
    
    return render(request, 'retail/dashboard.html', context)

def product_list(request):
    products = Артикулы.objects.select_related('id_группы').all()
    return render(request, 'retail/product_list.html', {'products': products})

def product_create(request):
    if request.method == 'POST':
        form = ProductForm(request.POST)
        if form.is_valid():
            form.save()
            return redirect('product_list')
    else:
        form = ProductForm()
    return render(request, 'retail/product_form.html', {'form': form, 'title': 'Добавить товар'})

def product_edit(request, pk):
    product = get_object_or_404(Артикулы, pk=pk)
    if request.method == 'POST':
        form = ProductForm(request.POST, instance=product)
        if form.is_valid():
            form.save()
            return redirect('product_list')
    else:
        form = ProductForm(instance=product)
    return render(request, 'retail/product_form.html', {'form': form, 'title': 'Редактировать товар'})

def product_delete(request, pk):
    product = get_object_or_404(Артикулы, pk=pk)
    if request.method == 'POST':
        product.delete()
        return redirect('product_list')
    return render(request, 'retail/product_confirm_delete.html', {'product': product})

def sale_list(request):
    sales = Клиенты.objects.select_related(
        'id_артикулы', 'id_скидки', 'id_группа'
    ).order_by('-дата_продажи')
    return render(request, 'retail/sale_list.html', {'sales': sales})

def sale_create(request):
    if request.method == 'POST':
        form = SaleForm(request.POST)
        if form.is_valid():
            form.save()
            return redirect('sale_list')
    else:
        form = SaleForm()
    return render(request, 'retail/sale_form.html', {'form': form, 'title': 'Добавить продажу'})

def sale_edit(request, pk):
    sale = get_object_or_404(Клиенты, pk=pk)
    if request.method == 'POST':
        form = SaleForm(request.POST, instance=sale)
        if form.is_valid():
            form.save()
            return redirect('sale_list')
    else:
        form = SaleForm(instance=sale)
    return render(request, 'retail/sale_form.html', {'form': form, 'title': 'Редактировать продажу'})

def sale_delete(request, pk):
    sale = get_object_or_404(Клиенты, pk=pk)
    if request.method == 'POST':
        sale.delete()
        return redirect('sale_list')
    return render(request, 'retail/sale_confirm_delete.html', {'sale': sale})

def analytics(request):
    # Продажи по месяцам
    with connection.cursor() as cursor:
        cursor.execute("""
            SELECT DATE_FORMAT(дата_продажи, '%%Y-%%m') as month,
                   COUNT(*) as count,
                   SUM(сумма_со_скидкой) as revenue
            FROM Клиенты
            GROUP BY DATE_FORMAT(дата_продажи, '%%Y-%%m')
            ORDER BY month DESC
            LIMIT 12
        """)
        monthly_sales = cursor.fetchall()
    
    # Продажи по группам товаров
    group_sales = Группа.objects.annotate(
        total_sales=Sum('клиенты__сумма_со_скидкой'),
        count_sales=Count('клиенты__id')
    ).order_by('-total_sales')
    
    # Продажи по городам
    city_sales = Города.objects.annotate(
        total_sales=Sum('скидки__клиенты__сумма_со_скидкой'),
        count_sales=Count('скидки__клиенты__id')
    ).order_by('-total_sales')
    
    # Средний чек по типам клиентов
    client_type_stats = ТипыКлиентов.objects.annotate(
        avg_check=Avg('скидки__клиенты__сумма_со_скидкой'),
        total_sales=Sum('скидки__клиенты__сумма_со_скидкой'),
        count_sales=Count('скидки__клиенты__id')
    ).order_by('-приоритет')
    
    context = {
        'monthly_sales': monthly_sales,
        'group_sales': group_sales,
        'city_sales': city_sales,
        'client_type_stats': client_type_stats,
    }
    
    return render(request, 'retail/analytics.html', context)

def chart_data(request):
    chart_type = request.GET.get('type', 'monthly')
    
    if chart_type == 'monthly':
        with connection.cursor() as cursor:
            cursor.execute("""
                SELECT DATE_FORMAT(дата_продажи, '%%Y-%%m') as month,
                       SUM(сумма_со_скидкой) as revenue
                FROM Клиенты
                GROUP BY DATE_FORMAT(дата_продажи, '%%Y-%%m')
                ORDER BY month
                LIMIT 12
            """)
            data = cursor.fetchall()
        
        labels = [row[0] for row in data]
        values = [float(row[1]) for row in data]
        
    elif chart_type == 'product_groups':
        data = Группа.objects.annotate(
            revenue=Sum('клиенты__сумма_со_скидкой')
        ).order_by('-revenue')[:10]
        
        labels = [group.наименование for group in data]
        values = [float(group.revenue or 0) for group in data]
    
    return JsonResponse({
        'labels': labels,
        'values': values,
        'type': chart_type
    })