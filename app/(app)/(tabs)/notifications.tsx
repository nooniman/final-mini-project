/**
 * Notifications Screen
 * Displays student notifications (grades posted, announcements, etc.)
 */

import React, { useEffect, useState } from 'react';
import { View, StyleSheet, FlatList, TouchableOpacity, RefreshControl } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { ThemedView } from '../../../src/components/ui/ThemedView';
import { ThemedText } from '../../../src/components/ui/ThemedText';
import { useTheme } from '../../../src/context/ThemeContext';
import { notificationsApi } from '../../../src/services/api';
import type { Notification } from '../../../src/types/notifications';

export default function NotificationsScreen() {
  const { colors } = useTheme();
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [refreshing, setRefreshing] = useState(false);

  const fetchNotifications = async () => {
    try {
      setIsLoading(true);
      const response = await notificationsApi.getAll();
      if (response.success && response.data) {
        // Backend returns { notifications: [...], unreadCount: number }
        const data = response.data as any;
        const notificationsList = data.notifications || data;
        // Normalize notifications: read_at -> isRead
        const normalized = Array.isArray(notificationsList) 
          ? notificationsList.map((n: any) => ({
              ...n,
              isRead: n.isRead !== undefined ? n.isRead : (n.read_at !== null && n.read_at !== undefined),
              createdAt: n.createdAt || n.created_at,
            }))
          : [];
        setNotifications(normalized);
      } else {
        setNotifications([]);
      }
    } catch (error) {
      console.error('Failed to fetch notifications:', error);
      setNotifications([]);
    } finally {
      setIsLoading(false);
    }
  };

  const handleRefresh = async () => {
    setRefreshing(true);
    await fetchNotifications();
    setRefreshing(false);
  };

  const handleMarkAsRead = async (id: string) => {
    try {
      await notificationsApi.markAsRead(id);
      setNotifications(prev => 
        prev.map(notif => 
          notif.id === id ? { ...notif, isRead: true } : notif
        )
      );
    } catch (error) {
      console.error('Failed to mark notification as read:', error);
    }
  };

  const handleMarkAllAsRead = async () => {
    try {
      await notificationsApi.markAllAsRead();
      setNotifications(prev => prev.map(notif => ({ ...notif, isRead: true })));
    } catch (error) {
      console.error('Failed to mark all notifications as read:', error);
    }
  };

  useEffect(() => {
    fetchNotifications();
  }, []);

  const getNotificationIcon = (type: string) => {
    switch (type) {
      case 'grade':
      case 'grade_posted':
        return 'school';
      case 'attendance':
      case 'attendance_alert':
        return 'calendar';
      case 'announcement':
        return 'megaphone';
      case 'reminder':
      case 'deadline_reminder':
        return 'alarm';
      case 'system':
      case 'system_update':
        return 'settings';
      default:
        return 'notifications';
    }
  };

  const renderNotification = ({ item }: { item: Notification }) => (
    <TouchableOpacity
      style={[
        styles.notificationCard,
        { 
          backgroundColor: item.isRead ? colors.surface : colors.card,
          borderColor: colors.border 
        }
      ]}
      onPress={() => !item.isRead && handleMarkAsRead(item.id)}
    >
      <View style={styles.iconContainer}>
        <View 
          style={[
            styles.iconCircle,
            { backgroundColor: item.isRead ? colors.border : colors.primary + '20' }
          ]}
        >
          <Ionicons 
            name={getNotificationIcon(item.type) as any}
            size={24} 
            color={item.isRead ? colors.textSecondary : colors.primary} 
          />
        </View>
      </View>
      
      <View style={styles.contentContainer}>
        <View style={styles.headerRow}>
          <ThemedText 
            variant="body" 
            weight={item.isRead ? 'regular' : 'semibold'}
            style={styles.title}
            numberOfLines={2}
          >
            {item.title}
          </ThemedText>
          {!item.isRead && (
            <View style={[styles.unreadBadge, { backgroundColor: colors.primary }]} />
          )}
        </View>
        
        <ThemedText 
          variant="body" 
          style={[styles.message, { color: colors.textSecondary }]}
          numberOfLines={3}
        >
          {item.message}
        </ThemedText>
        
        <View style={styles.metaRow}>
          <Ionicons name="time-outline" size={14} color={colors.textSecondary} />
          <ThemedText 
            variant="caption1" 
            style={[styles.timestamp, { color: colors.textSecondary }]}
          >
            {formatTimestamp(item.createdAt)}
          </ThemedText>
        </View>
      </View>
    </TouchableOpacity>
  );

  const unreadCount = Array.isArray(notifications) ? notifications.filter(n => !n.isRead).length : 0;

  return (
    <ThemedView style={styles.container}>
      {/* Header */}
      <View style={[styles.header, { backgroundColor: colors.surface, borderBottomColor: colors.border }]}>
        <View style={styles.headerContent}>
          <ThemedText variant="title1" weight="bold">Notifications</ThemedText>
          {unreadCount > 0 && (
            <View style={[styles.badge, { backgroundColor: colors.primary }]}>
              <ThemedText variant="caption1" weight="semibold" style={{ color: colors.surface }}>
                {unreadCount}
              </ThemedText>
            </View>
          )}
        </View>
        
        {unreadCount > 0 && (
          <TouchableOpacity onPress={handleMarkAllAsRead} style={styles.markAllButton}>
            <ThemedText variant="body" style={{ color: colors.primary }}>
              Mark all as read
            </ThemedText>
          </TouchableOpacity>
        )}
      </View>

      {/* Notifications List */}
      <FlatList
        data={notifications}
        renderItem={renderNotification}
        keyExtractor={item => item.id}
        contentContainerStyle={styles.listContent}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={handleRefresh}
            tintColor={colors.primary}
          />
        }
        ListEmptyComponent={
          <View style={styles.emptyContainer}>
            <Ionicons name="notifications-off-outline" size={64} color={colors.textSecondary} />
            <ThemedText variant="headline" style={{ color: colors.textSecondary, marginTop: 16 }}>
              No notifications yet
            </ThemedText>
            <ThemedText variant="body" style={{ color: colors.textSecondary, marginTop: 8, textAlign: 'center' }}>
              You'll see updates about grades, attendance, and announcements here
            </ThemedText>
          </View>
        }
      />
    </ThemedView>
  );
}

function formatTimestamp(timestamp: string): string {
  const date = new Date(timestamp);
  const now = new Date();
  const diffMs = now.getTime() - date.getTime();
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);

  if (diffMins < 1) return 'Just now';
  if (diffMins < 60) return `${diffMins}m ago`;
  if (diffHours < 24) return `${diffHours}h ago`;
  if (diffDays < 7) return `${diffDays}d ago`;
  
  return date.toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric',
    year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined 
  });
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  header: {
    paddingTop: 60,
    paddingHorizontal: 20,
    paddingBottom: 16,
    borderBottomWidth: 1,
  },
  headerContent: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    marginBottom: 12,
  },
  badge: {
    minWidth: 24,
    height: 24,
    borderRadius: 12,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 8,
  },
  markAllButton: {
    alignSelf: 'flex-start',
  },
  listContent: {
    padding: 16,
    paddingBottom: 32,
  },
  notificationCard: {
    flexDirection: 'row',
    padding: 16,
    borderRadius: 12,
    marginBottom: 12,
    borderWidth: 1,
  },
  iconContainer: {
    marginRight: 12,
  },
  iconCircle: {
    width: 48,
    height: 48,
    borderRadius: 24,
    alignItems: 'center',
    justifyContent: 'center',
  },
  contentContainer: {
    flex: 1,
  },
  headerRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    marginBottom: 6,
  },
  title: {
    flex: 1,
    marginRight: 8,
  },
  unreadBadge: {
    width: 8,
    height: 8,
    borderRadius: 4,
    marginTop: 6,
  },
  message: {
    marginBottom: 8,
    lineHeight: 20,
  },
  metaRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
  },
  timestamp: {
    fontSize: 12,
  },
  emptyContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 80,
    paddingHorizontal: 32,
  },
});
