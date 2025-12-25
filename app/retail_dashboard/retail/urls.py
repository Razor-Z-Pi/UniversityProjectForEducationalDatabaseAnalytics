# retail/urls.py
from django.urls import path
from . import views

urlpatterns = [
    path('', views.index, name='index'),
    path('dashboard/', views.dashboard, name='dashboard'),
    
    # Товары
    path('products/', views.product_list, name='product_list'),
    path('products/create/', views.product_create, name='product_create'),
    path('products/edit/<int:pk>/', views.product_edit, name='product_edit'),
    path('products/delete/<int:pk>/', views.product_delete, name='product_delete'),
    
    # Продажи
    path('sales/', views.sale_list, name='sale_list'),
    path('sales/create/', views.sale_create, name='sale_create'),
    path('sales/edit/<int:pk>/', views.sale_edit, name='sale_edit'),
    path('sales/delete/<int:pk>/', views.sale_delete, name='sale_delete'),
    
    # Аналитика
    path('analytics/', views.analytics, name='analytics'),
    path('chart-data/', views.chart_data, name='chart_data'),
]

# retail_dashboard/urls.py (главный)
from django.contrib import admin
from django.urls import path, include

urlpatterns = [
    path('admin/', admin.site.urls),
    path('', include('retail.urls')),
]