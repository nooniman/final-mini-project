/**
 * Subjects Screen
 * Displays enrolled subjects with schedule information from API
 */

import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  StyleSheet,
  ScrollView,
  RefreshControl,
  TouchableOpacity,
  ActivityIndicator,
} from 'react-native';
import { router } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { useTheme } from '../../../src/context/ThemeContext';
import { Layout } from '../../../src/constants/Layout';
import { subjectsApi } from '../../../src/services/api/subjects';
import {
  ThemedView,
  ThemedText,
  Card,
  Badge,
  Header,
  EmptyState,
} from '../../../src/components/ui';

interface Schedule {
  day: string;
  start_time: string;
  end_time: string;
  room: string;
  type: string;
}

interface Subject {
  id: number;
  code: string;
  name: string;
  units: number;
  instructor_first_name?: string;
  instructor_last_name?: string;
  schedules: Schedule[];
}

const daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

// Helper to format time from 24h to 12h
const formatTime = (time: string): string => {
  if (!time) return '';
  const [hours, minutes] = time.split(':');
  const hour = parseInt(hours, 10);
  const ampm = hour >= 12 ? 'PM' : 'AM';
  const hour12 = hour % 12 || 12;
  return `${hour12}:${minutes} ${ampm}`;
};

export default function SubjectsScreen() {
  const { colors } = useTheme();
  const [refreshing, setRefreshing] = useState(false);
  const [loading, setLoading] = useState(true);
  const [viewMode, setViewMode] = useState<'list' | 'schedule'>('list');
  const [subjects, setSubjects] = useState<Subject[]>([]);
  const [error, setError] = useState<string | null>(null);

  const fetchSubjects = useCallback(async () => {
    try {
      setError(null);
      const response = await subjectsApi.getAll();
      console.log('📚 Loaded subjects:', response);
      if (response.success && response.data) {
        setSubjects(response.data as any);
      } else {
        setError(response.message || 'Failed to load subjects');
      }
    } catch (err: any) {
      console.error('Failed to fetch subjects:', err);
      setError(err.message || 'Failed to load subjects');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchSubjects();
  }, [fetchSubjects]);

  const onRefresh = async () => {
    setRefreshing(true);
    await fetchSubjects();
    setRefreshing(false);
  };

  const totalUnits = subjects.reduce((sum, s) => sum + (s.units || 0), 0);

  // Get instructor display name
  const getInstructorName = (subject: Subject): string => {
    if (subject.instructor_first_name && subject.instructor_last_name) {
      return `${subject.instructor_first_name} ${subject.instructor_last_name}`;
    }
    return 'TBA';
  };

  // Get schedule by day
  const getScheduleForDay = (day: string) => {
    const schedules: Array<{ subject: Subject; schedule: Schedule }> = [];
    
    subjects.forEach((subject) => {
      if (subject.schedules) {
        subject.schedules.forEach((sched) => {
          if (sched.day === day) {
            schedules.push({ subject, schedule: sched });
          }
        });
      }
    });

    return schedules.sort((a, b) => 
      (a.schedule.start_time || '').localeCompare(b.schedule.start_time || '')
    );
  };

  if (loading) {
    return (
      <ThemedView style={[styles.container, styles.centered]}>
        <ActivityIndicator size="large" color={colors.primary} />
        <ThemedText variant="body" color="secondary" style={{ marginTop: 16 }}>
          Loading subjects...
        </ThemedText>
      </ThemedView>
    );
  }

  return (
    <ThemedView style={styles.container}>
      <Header
        title="Subjects"
        large
        rightAction={
          <TouchableOpacity
            onPress={() => setViewMode(viewMode === 'list' ? 'schedule' : 'list')}
            style={styles.viewToggle}
          >
            <Ionicons
              name={viewMode === 'list' ? 'calendar-outline' : 'list-outline'}
              size={24}
              color={colors.primary}
            />
          </TouchableOpacity>
        }
      />

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={onRefresh}
            tintColor={colors.primary}
          />
        }
      >
        {error ? (
          <EmptyState
            icon="alert-circle-outline"
            title="Error Loading Subjects"
            message={error}
            actionLabel="Try Again"
            onAction={fetchSubjects}
          />
        ) : subjects.length === 0 ? (
          <EmptyState
            icon="book-outline"
            title="No Subjects Found"
            message="You are not enrolled in any subjects for this semester."
          />
        ) : (
          <>
            {/* Summary Card */}
            <Card variant="elevated" style={styles.summaryCard}>
              <View style={styles.summaryRow}>
                <View style={styles.summaryItem}>
                  <ThemedText variant="title1" color="accent">
                    {subjects.length}
                  </ThemedText>
                  <ThemedText variant="caption1" color="secondary">
                    Subjects
                  </ThemedText>
                </View>
                <View style={[styles.summaryDivider, { backgroundColor: colors.divider }]} />
                <View style={styles.summaryItem}>
                  <ThemedText variant="title1" color="accent">
                    {totalUnits}
                  </ThemedText>
                  <ThemedText variant="caption1" color="secondary">
                    Total Units
                  </ThemedText>
                </View>
                <View style={[styles.summaryDivider, { backgroundColor: colors.divider }]} />
                <View style={styles.summaryItem}>
                  <ThemedText variant="title1" color="accent">
                    2nd
                  </ThemedText>
                  <ThemedText variant="caption1" color="secondary">
                    Semester
                  </ThemedText>
                </View>
              </View>
            </Card>

            {viewMode === 'list' ? (
              // List View
              <View style={styles.subjectsSection}>
                <ThemedText variant="title3" style={styles.sectionTitle}>
                  Enrolled Subjects
                </ThemedText>

                {subjects.map((subject) => (
                  <Card
                    key={subject.id}
                    variant="elevated"
                    style={styles.subjectCard}
                    onPress={() => router.push(`/(app)/subjects/${subject.id}`)}
                  >
                    <View style={styles.subjectHeader}>
                      <View style={styles.subjectInfo}>
                        <View style={styles.subjectTitleRow}>
                          <ThemedText variant="headline">{subject.name}</ThemedText>
                        </View>
                        <ThemedText variant="caption1" color="secondary">
                          {subject.code} • {subject.units} units
                        </ThemedText>
                      </View>
                      <Ionicons
                        name="chevron-forward"
                        size={20}
                        color={colors.textTertiary}
                      />
                    </View>

                    <View style={[styles.subjectDivider, { backgroundColor: colors.divider }]} />

                    <View style={styles.scheduleList}>
                      {subject.schedules && subject.schedules.map((sched, index) => (
                        <View key={index} style={styles.scheduleItem}>
                          <View style={styles.scheduleDay}>
                            <ThemedText variant="caption1" weight="semibold">
                              {sched.day?.substring(0, 3) || 'TBA'}
                            </ThemedText>
                          </View>
                          <View style={styles.scheduleDetails}>
                            <ThemedText variant="subheadline">
                              {formatTime(sched.start_time)} - {formatTime(sched.end_time)}
                            </ThemedText>
                            <View style={styles.scheduleLocation}>
                              <Ionicons
                                name="location-outline"
                                size={12}
                                color={colors.textTertiary}
                              />
                              <ThemedText variant="caption2" color="tertiary">
                                {sched.room || 'TBA'}
                              </ThemedText>
                              <Badge
                                label={sched.type || 'Lecture'}
                                variant={sched.type === 'Laboratory' ? 'info' : 'secondary'}
                                size="sm"
                              />
                            </View>
                          </View>
                        </View>
                      ))}
                      {(!subject.schedules || subject.schedules.length === 0) && (
                        <ThemedText variant="caption1" color="tertiary">
                          No schedule set
                        </ThemedText>
                      )}
                    </View>

                    <View style={styles.instructorRow}>
                      <Ionicons
                        name="person-outline"
                        size={14}
                        color={colors.textTertiary}
                      />
                      <ThemedText variant="caption1" color="tertiary">
                        {getInstructorName(subject)}
                      </ThemedText>
                    </View>
                  </Card>
                ))}
              </View>
            ) : (
              // Schedule View
              <View style={styles.scheduleSection}>
                <ThemedText variant="title3" style={styles.sectionTitle}>
                  Weekly Schedule
                </ThemedText>

                {daysOfWeek.map((day) => {
                  const daySchedule = getScheduleForDay(day);
                  if (daySchedule.length === 0) return null;

                  return (
                    <View key={day} style={styles.daySection}>
                      <ThemedText variant="headline" style={styles.dayTitle}>
                        {day}
                      </ThemedText>
                      
                      {daySchedule.map((item, index) => (
                        <Card key={index} variant="outlined" style={styles.scheduleCard}>
                          <View style={styles.scheduleCardRow}>
                            <View
                              style={[
                                styles.timeBlock,
                                { backgroundColor: colors.primaryMuted },
                              ]}
                            >
                              <ThemedText variant="caption1" color="accent" weight="semibold">
                                {formatTime(item.schedule.start_time)}
                              </ThemedText>
                              <ThemedText variant="caption2" color="secondary">
                                {formatTime(item.schedule.end_time)}
                              </ThemedText>
                            </View>
                            <View style={styles.scheduleCardInfo}>
                              <ThemedText variant="headline">{item.subject.name}</ThemedText>
                              <ThemedText variant="caption1" color="secondary">
                                {item.subject.code} • {item.schedule.room || 'TBA'}
                              </ThemedText>
                              <Badge
                                label={item.schedule.type || 'Lecture'}
                                variant={item.schedule.type === 'Laboratory' ? 'info' : 'secondary'}
                                size="sm"
                              />
                            </View>
                          </View>
                        </Card>
                      ))}
                    </View>
                  );
                })}
              </View>
            )}
          </>
        )}

        <View style={{ height: Layout.spacing.xl }} />
      </ScrollView>
    </ThemedView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  centered: {
    justifyContent: 'center',
    alignItems: 'center',
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    paddingHorizontal: Layout.padding.horizontal,
  },
  viewToggle: {
    padding: Layout.spacing.sm,
  },
  summaryCard: {
    marginBottom: Layout.spacing.lg,
  },
  summaryRow: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: Layout.spacing.md,
  },
  summaryItem: {
    flex: 1,
    alignItems: 'center',
  },
  summaryDivider: {
    width: 1,
    height: 40,
  },
  subjectsSection: {
    marginBottom: Layout.spacing.lg,
  },
  sectionTitle: {
    marginBottom: Layout.spacing.sm,
  },
  subjectCard: {
    marginBottom: Layout.spacing.sm,
  },
  subjectHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: Layout.spacing.md,
  },
  subjectInfo: {
    flex: 1,
  },
  subjectTitleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    flexWrap: 'wrap',
  },
  subjectDivider: {
    height: StyleSheet.hairlineWidth,
    marginHorizontal: Layout.spacing.md,
  },
  scheduleList: {
    padding: Layout.spacing.md,
    gap: Layout.spacing.sm,
  },
  scheduleItem: {
    flexDirection: 'row',
    alignItems: 'flex-start',
  },
  scheduleDay: {
    width: 36,
    marginRight: Layout.spacing.sm,
  },
  scheduleDetails: {
    flex: 1,
  },
  scheduleLocation: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    marginTop: 2,
  },
  instructorRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    paddingHorizontal: Layout.spacing.md,
    paddingBottom: Layout.spacing.md,
  },
  scheduleSection: {
    marginBottom: Layout.spacing.lg,
  },
  daySection: {
    marginBottom: Layout.spacing.md,
  },
  dayTitle: {
    marginBottom: Layout.spacing.sm,
  },
  scheduleCard: {
    marginBottom: Layout.spacing.xs,
  },
  scheduleCardRow: {
    flexDirection: 'row',
    padding: Layout.spacing.md,
  },
  timeBlock: {
    paddingHorizontal: Layout.spacing.sm,
    paddingVertical: Layout.spacing.xs,
    borderRadius: Layout.borderRadius.sm,
    marginRight: Layout.spacing.md,
    alignItems: 'center',
    minWidth: 65,
  },
  scheduleCardInfo: {
    flex: 1,
    gap: 2,
  },
});
