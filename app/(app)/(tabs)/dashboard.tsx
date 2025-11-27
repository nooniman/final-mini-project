/**
 * Dashboard Screen
 * Home screen with overview of student's academic status
 */

import React, { useEffect, useState, useCallback } from 'react';
import { View, StyleSheet, ScrollView, RefreshControl, TouchableOpacity, ActivityIndicator } from 'react-native';
import { router } from 'expo-router';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import { useTheme } from '../../../src/context/ThemeContext';
import { useAuthStore } from '../../../src/store/useAuthStore';
import { Layout } from '../../../src/constants/Layout';
import {
  ThemedView,
  ThemedText,
  Card,
  Avatar,
  Badge,
} from '../../../src/components/ui';
import { subjectsApi } from '../../../src/services/api/subjects';
import { gradesApi } from '../../../src/services/api/grades';

interface QuickStat {
  label: string;
  value: string;
  icon: string;
  color: string;
}

interface RecentGrade {
  subject: string;
  code: string;
  grade: string;
  status: string;
}

interface UpcomingClass {
  subject: string;
  time: string;
  room: string;
  type: string;
}

export default function DashboardScreen() {
  const { colors } = useTheme();
  const insets = useSafeAreaInsets();
  const { user } = useAuthStore();
  const [refreshing, setRefreshing] = useState(false);
  const [loading, setLoading] = useState(true);
  
  // State for API data
  const [quickStats, setQuickStats] = useState<QuickStat[]>([
    { label: 'Current GWA', value: '--', icon: 'trophy', color: '#34C759' },
    { label: 'Units Enrolled', value: '--', icon: 'book', color: '#007AFF' },
    { label: 'Attendance', value: '95%', icon: 'calendar', color: '#FF9500' },
    { label: 'Subjects', value: '--', icon: 'grid', color: '#AF52DE' },
  ]);
  const [recentGrades, setRecentGrades] = useState<RecentGrade[]>([]);
  const [upcomingClasses, setUpcomingClasses] = useState<UpcomingClass[]>([]);

  const fetchDashboardData = useCallback(async () => {
    try {
      console.log('📊 Fetching dashboard data...');
      
      // Fetch subjects
      const subjectsResponse = await subjectsApi.getAll();
      const subjects = subjectsResponse.data || [];
      
      // Fetch grades
      const gradesResponse = await gradesApi.getAll();
      const grades = gradesResponse.data || [];
      
      // Calculate GWA from grades
      let totalUnits = 0;
      let weightedSum = 0;
      const gradesWithValues = grades.filter((g: any) => g.grade !== null && g.grade !== undefined);
      
      gradesWithValues.forEach((g: any) => {
        const units = g.units || 3;
        const gradeValue = parseFloat(g.grade);
        if (!isNaN(gradeValue)) {
          totalUnits += units;
          weightedSum += gradeValue * units;
        }
      });
      
      const gwa = totalUnits > 0 ? (weightedSum / totalUnits).toFixed(2) : '--';
      
      // Calculate enrolled units
      const enrolledUnits = subjects.reduce((sum: number, s: any) => sum + (s.units || 3), 0);
      
      // Update quick stats
      setQuickStats([
        { label: 'Current GWA', value: gwa, icon: 'trophy', color: '#34C759' },
        { label: 'Units Enrolled', value: enrolledUnits.toString(), icon: 'book', color: '#007AFF' },
        { label: 'Attendance', value: '95%', icon: 'calendar', color: '#FF9500' },
        { label: 'Subjects', value: subjects.length.toString(), icon: 'grid', color: '#AF52DE' },
      ]);
      
      // Format recent grades (show last 3)
      const formattedGrades = grades.slice(0, 3).map((g: any) => ({
        subject: g.subject_name || g.name || 'Unknown Subject',
        code: g.subject_code || g.code || 'N/A',
        grade: g.grade ? g.grade.toString() : 'In Progress',
        status: g.grade ? 'viewed' : 'new',
      }));
      setRecentGrades(formattedGrades);
      
      // Format upcoming classes from subjects with schedule
      const today = new Date();
      const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
      const todayName = dayNames[today.getDay()];
      
      const todayClasses = subjects
        .filter((s: any) => s.schedule && s.schedule.toLowerCase().includes(todayName.toLowerCase().substring(0, 3)))
        .map((s: any) => ({
          subject: s.name || s.subject_name,
          time: s.time || '8:00 AM',
          room: s.room || 'TBA',
          type: s.type || 'Lecture',
        }));
      
      setUpcomingClasses(todayClasses.length > 0 ? todayClasses : [
        { subject: 'No classes today', time: '--', room: '--', type: '' }
      ]);
      
    } catch (error) {
      console.error('❌ Error fetching dashboard data:', error);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchDashboardData();
  }, [fetchDashboardData]);

  const onRefresh = useCallback(async () => {
    setRefreshing(true);
    await fetchDashboardData();
    setRefreshing(false);
  }, [fetchDashboardData]);

  const greeting = () => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good Morning';
    if (hour < 17) return 'Good Afternoon';
    return 'Good Evening';
  };

  if (loading) {
    return (
      <ThemedView style={[styles.container, styles.loadingContainer]}>
        <ActivityIndicator size="large" color={colors.primary} />
        <ThemedText variant="subheadline" color="secondary" style={{ marginTop: 16 }}>
          Loading dashboard...
        </ThemedText>
      </ThemedView>
    );
  }

  return (
    <ThemedView style={styles.container}>
      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={[
          styles.scrollContent,
          { paddingTop: insets.top + Layout.spacing.md },
        ]}
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={onRefresh}
            tintColor={colors.primary}
          />
        }
      >
        {/* Header */}
        <View style={styles.header}>
          <View style={styles.headerLeft}>
            <ThemedText variant="subheadline" color="secondary">
              {greeting()},
            </ThemedText>
            <ThemedText variant="title2">
              {user?.firstName || 'Student'}
            </ThemedText>
          </View>
          <TouchableOpacity onPress={() => router.push('/(app)/settings')}>
            <Avatar
              name={user?.fullName || 'Student'}
              source={user?.avatar}
              size="md"
            />
          </TouchableOpacity>
        </View>

        {/* Quick Stats */}
        <View style={styles.statsGrid}>
          {quickStats.map((stat, index) => (
            <Card key={index} variant="elevated" style={styles.statCard}>
              <View
                style={[
                  styles.statIcon,
                  { backgroundColor: `${stat.color}20` },
                ]}
              >
                <Ionicons
                  name={stat.icon as any}
                  size={20}
                  color={stat.color}
                />
              </View>
              <ThemedText variant="title2" style={styles.statValue}>
                {stat.value}
              </ThemedText>
              <ThemedText variant="caption1" color="secondary">
                {stat.label}
              </ThemedText>
            </Card>
          ))}
        </View>

        {/* Recent Grades */}
        <View style={styles.section}>
          <View style={styles.sectionHeader}>
            <ThemedText variant="title3">Recent Grades</ThemedText>
            <TouchableOpacity onPress={() => router.push('/(app)/(tabs)/grades')}>
              <ThemedText variant="subheadline" color="accent">
                See All
              </ThemedText>
            </TouchableOpacity>
          </View>

          <Card variant="elevated">
            {recentGrades.length > 0 ? (
              recentGrades.map((grade, index) => (
                <TouchableOpacity
                  key={index}
                  style={[
                    styles.gradeItem,
                    index !== recentGrades.length - 1 && {
                      borderBottomWidth: StyleSheet.hairlineWidth,
                      borderBottomColor: colors.divider,
                    },
                  ]}
                >
                  <View style={styles.gradeInfo}>
                    <ThemedText variant="headline">{grade.subject}</ThemedText>
                    <ThemedText variant="caption1" color="secondary">
                      {grade.code}
                    </ThemedText>
                  </View>
                  <View style={styles.gradeRight}>
                    {grade.status === 'new' && (
                      <Badge label="New" variant="primary" size="sm" />
                    )}
                    <ThemedText variant="title3" color="accent">
                      {grade.grade}
                    </ThemedText>
                  </View>
                </TouchableOpacity>
              ))
            ) : (
              <View style={styles.gradeItem}>
                <ThemedText variant="body" color="secondary">
                  No grades available yet
                </ThemedText>
              </View>
            )}
          </Card>
        </View>

        {/* Today's Schedule */}
        <View style={styles.section}>
          <View style={styles.sectionHeader}>
            <ThemedText variant="title3">Today's Classes</ThemedText>
            <TouchableOpacity onPress={() => router.push('/(app)/(tabs)/subjects')}>
              <ThemedText variant="subheadline" color="accent">
                Full Schedule
              </ThemedText>
            </TouchableOpacity>
          </View>

          {upcomingClasses.map((cls: UpcomingClass, index: number) => (
            <Card
              key={index}
              variant="elevated"
              style={styles.scheduleCard}
              padding="md"
            >
              <View style={styles.scheduleRow}>
                <View
                  style={[
                    styles.scheduleTime,
                    { backgroundColor: colors.primaryMuted },
                  ]}
                >
                  <ThemedText variant="caption1" color="accent" weight="semibold">
                    {cls.time}
                  </ThemedText>
                </View>
                <View style={styles.scheduleInfo}>
                  <ThemedText variant="headline">{cls.subject}</ThemedText>
                  <View style={styles.scheduleDetails}>
                    <Ionicons
                      name="location-outline"
                      size={14}
                      color={colors.textSecondary}
                    />
                    <ThemedText variant="caption1" color="secondary">
                      {cls.room}
                    </ThemedText>
                    <ThemedText variant="caption1" color="tertiary">
                      • {cls.type}
                    </ThemedText>
                  </View>
                </View>
                <Ionicons
                  name="chevron-forward"
                  size={20}
                  color={colors.textTertiary}
                />
              </View>
            </Card>
          ))}
        </View>

        {/* Notifications Banner */}
        <Card
          variant="elevated"
          style={[styles.notificationBanner, { backgroundColor: colors.primaryMuted }]}
          padding="md"
        >
          <View style={styles.notificationContent}>
            <Ionicons name="notifications" size={24} color={colors.primary} />
            <View style={styles.notificationText}>
              <ThemedText variant="headline">
                Enrollment Period Open
              </ThemedText>
              <ThemedText variant="caption1" color="secondary">
                Second Semester 2024-2025 enrollment is now open
              </ThemedText>
            </View>
          </View>
        </Card>

        <View style={{ height: Layout.spacing.xl }} />
      </ScrollView>
    </ThemedView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  loadingContainer: {
    justifyContent: 'center',
    alignItems: 'center',
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    paddingHorizontal: Layout.padding.horizontal,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: Layout.spacing.lg,
  },
  headerLeft: {
    flex: 1,
  },
  statsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    marginHorizontal: -Layout.spacing.xs,
    marginBottom: Layout.spacing.lg,
  },
  statCard: {
    width: '48%',
    marginHorizontal: '1%',
    marginBottom: Layout.spacing.sm,
    alignItems: 'center',
    paddingVertical: Layout.spacing.md,
  },
  statIcon: {
    width: 40,
    height: 40,
    borderRadius: 20,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: Layout.spacing.sm,
  },
  statValue: {
    marginBottom: 2,
  },
  section: {
    marginBottom: Layout.spacing.lg,
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: Layout.spacing.sm,
  },
  gradeItem: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: Layout.spacing.md,
  },
  gradeInfo: {
    flex: 1,
  },
  gradeRight: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: Layout.spacing.sm,
  },
  scheduleCard: {
    marginBottom: Layout.spacing.sm,
  },
  scheduleRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  scheduleTime: {
    paddingHorizontal: Layout.spacing.sm,
    paddingVertical: Layout.spacing.xs,
    borderRadius: Layout.borderRadius.sm,
    marginRight: Layout.spacing.md,
  },
  scheduleInfo: {
    flex: 1,
  },
  scheduleDetails: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    marginTop: 2,
  },
  notificationBanner: {
    marginBottom: Layout.spacing.md,
  },
  notificationContent: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  notificationText: {
    flex: 1,
    marginLeft: Layout.spacing.md,
  },
});
